<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    public function product() { return $this->belongsTo(Product::class); }
    public function prices() { return $this->hasMany(Price::class); }
    public function inventoryLots() { return $this->hasMany(InventoryLot::class); }
    public function currentPrices(): HasMany
    {
        return $this->prices()
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            })
            ->orderBy('priority', 'asc') // Prioridad 1 gana
            ->orderBy('min_quantity', 'desc'); // A igualdad de prioridad, preferir escalas mayores (opcional)
    }
    protected function displayPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->relationLoaded('currentPrices')) {
                    return $this->base_price;
                }

                $price = $this->currentPrices
                    ->where('min_quantity', '<=', 1)
                    ->first();

                // Aseguramos que devuelva el valor numérico
                return $price ? $price->final_price : $this->base_price;
            }
        );
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