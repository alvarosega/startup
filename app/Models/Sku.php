<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\HasUuidv7;

class Sku extends Model
{
    use HasFactory, SoftDeletes, HasUuidv7;

    protected $fillable = [
        'id',
        'product_id',
        'name',
        'code',
        'base_price',
        'conversion_factor',
        'weight',
        'image_path',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'conversion_factor' => 'decimal:3', // Cambiado a 3 decimales
        'weight' => 'decimal:3'
    ];

    protected $appends = ['image_url'];

    // Relación con Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    

    // Relación con Prices
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'sku_id', 'id');
    }

    // Relación con InventoryLots
    public function inventoryLots(): HasMany
    {
        return $this->hasMany(InventoryLot::class, 'sku_id', 'id');
    }

    // Accessor para la URL de la imagen
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }
        
        // Fallback a la imagen del producto si existe
        if ($this->relationLoaded('product') && $this->product && $this->product->image_path) {
            return Storage::url($this->product->image_path);
        }
        
        return null;
    }

    // Método para actualizar precio
    public function updatePrice(float $newPrice, $branchId = null)
    {
        // Buscar el precio activo actual
        $currentPrice = $this->prices()
            ->where('branch_id', $branchId)
            ->first();

        // Si existe y el precio es diferente, eliminarlo (soft delete)
        if ($currentPrice && floatval($currentPrice->final_price) !== floatval($newPrice)) {
            $currentPrice->delete();
        }

        // Crear nuevo precio
        $this->prices()->create([
            'branch_id' => $branchId,
            'list_price' => $newPrice * 1.10,
            'final_price' => $newPrice,
            'min_quantity' => 1,
            'valid_from' => now()
        ]);
    }

    // Helper para obtener el precio actual
    public function currentPrice()
    {
        return $this->hasOne(Price::class, 'sku_id', 'id')
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->latest('valid_from');
    }

    // Obtener precio para una sucursal específica o nacional
    public function getCurrentPrice($branchId = null)
    {
        $prices = $this->prices ?? collect();

        if ($branchId) {
            $branchPrice = $prices->where('branch_id', $branchId)->first();
            if ($branchPrice) return (float) $branchPrice->final_price;
        }

        $basePrice = $prices->whereNull('branch_id')->first();
        return $basePrice ? (float) $basePrice->final_price : 0.00;
    }

    // Obtener precio para sucursal con fallback a nacional
    public function getPriceForBranch(?int $branchId = null)
    {
        if ($branchId) {
            $branchPrice = $this->prices()
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            if ($branchPrice) {
                return $branchPrice;
            }
        }

        return $this->prices()
            ->whereNull('branch_id')
            ->orderBy('id', 'desc')
            ->first();
    }
    /**
     * Calcula el precio vigente para una sucursal específica.
     * Prioridad: Precio Sucursal > Precio Nacional (branch_id null)
     * Usado por el ShopProductResource.
     */
    public function getContextPrice(?int $branchId): float
    {
        // Buscamos en la colección ya cargada (eager loaded) para evitar N+1 queries
        $prices = $this->relationLoaded('prices') ? $this->prices : $this->prices()->get();

        $price = $prices->first(function ($p) use ($branchId) {
            return $p->branch_id == $branchId;
        }) ?? $prices->first(function ($p) {
            return $p->branch_id === null;
        });

        return $price ? (float) $price->final_price : 0.00;
    }

    /**
     * Calcula el stock disponible para una sucursal específica.
     * Usado por el ShopProductResource.
     */
    public function getContextStock(?int $branchId): int
    {
        if (!$branchId) return 0;

        // Usamos la colección cargada o consultamos si no existe
        $lots = $this->relationLoaded('inventoryLots') ? $this->inventoryLots : $this->inventoryLots()->where('branch_id', $branchId)->get();

        return (int) $lots
            ->where('branch_id', $branchId)
            ->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);
    }   

}