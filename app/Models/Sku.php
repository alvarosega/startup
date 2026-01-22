<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Sku extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'code', 'name', 
        'weight', 'conversion_factor', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conversion_factor' => 'decimal:2',
        'weight' => 'decimal:3'
    ];

        

    public function updatePrice(float $newPrice)
    {
        // Buscamos el último precio registrado
        $latestPrice = $this->prices()->latest('id')->first();

        // Si no tiene precio O el precio cambió, registramos/actualizamos
        if (!$latestPrice || floatval($latestPrice->final_price) !== floatval($newPrice)) {
            
            // OPCIÓN A: Sobreescribir el último si fue creado hoy (Para corregir errores)
            // if ($latestPrice && $latestPrice->created_at->isToday()) { ... }

            // OPCIÓN B (Elegida): Crear nuevo registro histórico siempre (Auditoría)
            // O actualización directa para MVP simple:
            $this->prices()->create([
                'branch_id' => null, // Precio Base Nacional
                'list_price' => $newPrice * 1.10, // Margen teórico
                'final_price' => $newPrice,
                'min_quantity' => 1,
                'valid_from' => now()
            ]);
        }
    }



    use SoftDeletes;
    protected $guarded = [];

    // Relación Inversa
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con Precios (Tabla 'prices')
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    // Helper para obtener el precio actual vigente
    public function currentPrice()
    {
        return $this->hasOne(Price::class)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->latest('valid_from'); // Prioriza el más reciente
    }
    
    // Accessor para obtener la imagen correcta (SKU o Producto Padre)
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_path 
                ? '/storage/' . $this->image_path 
                : ($this->product->image_path ? '/storage/' . $this->product->image_path : '/images/default-product.png')
        );
    }


}