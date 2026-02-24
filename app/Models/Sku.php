<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Sku extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id', 'name', 'code', 'base_price', 
        'conversion_factor', 'weight', 'image_path', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'conversion_factor' => 'decimal:3',
        'weight' => 'decimal:3'
    ];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    
    public function prices(): HasMany { return $this->hasMany(Price::class); }

    /**
     * Relación con los lotes de inventario.
     * Vital para el filtrado de stock por sucursal en el Action.
     */
    public function inventoryLots(): HasMany 
    { 
        return $this->hasMany(InventoryLot::class, 'sku_id'); 
    }

    // =================================================================================
    // ACCESSORS SENIOR
    // =================================================================================

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
    public static function getAvailableForBundles()
    {
        // Eager loading de 'product' es obligatorio para el label del select
        return self::with('product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'product_id', 'name', 'base_price']); // Solo columnas necesarias
    }
}