<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Concerns\HasUuidv7;

class Sku extends Model
{
    use HasFactory, SoftDeletes, HasUuidv7;

    protected $fillable = [
        'product_id', 'code', 'name', 
        'weight', 'conversion_factor', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conversion_factor' => 'decimal:2',
        'weight' => 'decimal:3'
    ];
    protected $appends = ['image'];
        
/**
     * Relación con Lotes de Inventario.
     * Necesaria para filtrar stock disponible.
     */
    public function inventoryLots()
    {
        return $this->hasMany(InventoryLot::class);
    }
    public function updatePrice(float $newPrice, $branchId = null)
    {
        // 1. Buscar el precio ACTIVO actual para este contexto
        // (Eloquent por defecto solo busca where deleted_at IS NULL)
        $currentPrice = $this->prices()
            ->where('branch_id', $branchId)
            ->first();

        // 2. Si existe, lo enviamos al historial (Soft Delete)
        if ($currentPrice) {
            // Pequeña optimización: Si el precio es idéntico, no hacemos nada para no llenar la BD
            if (floatval($currentPrice->final_price) === floatval($newPrice)) {
                return;
            }
            $currentPrice->delete(); 
        }

        // 3. Crear el nuevo precio vigente
        $this->prices()->create([
            'branch_id' => $branchId,
            'list_price' => $newPrice * 1.10, // Margen teórico
            'final_price' => $newPrice,
            'min_quantity' => 1,
            'valid_from' => now()
        ]);
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
/**
     * Helper lógico: Retorna el precio final (float) 
     * discriminando si es para una sucursal específica o nacional.
     */
    public function getCurrentPrice($branchId = null)
    {
        // La relación 'prices' ya viene filtrada por SoftDeletes (solo activos)
        // si usas $this->prices (Eager Loading) o $this->prices()->get().
        
        $prices = $this->prices ?? collect();

        // 1. Prioridad: Precio Sucursal
        if ($branchId) {
            $branchPrice = $prices->where('branch_id', $branchId)->first();
            if ($branchPrice) return (float) $branchPrice->final_price;
        }

        // 2. Fallback: Precio Base Nacional
        $basePrice = $prices->whereNull('branch_id')->first();

        return $basePrice ? (float) $basePrice->final_price : 0.00;
    }
    public function getPriceForBranch(?int $branchId = null)
    {
        // 1. Si especificaron sucursal, intentamos buscar precio específico
        if ($branchId) {
            $branchPrice = $this->prices()
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc') // El más reciente si hubiera duplicados
                ->first();

            if ($branchPrice) {
                return $branchPrice;
            }
        }

        // 2. Fallback: Retornar precio Nacional (branch_id IS NULL)
        return $this->prices()
            ->whereNull('branch_id')
            ->orderBy('id', 'desc')
            ->first();
    }
    

}