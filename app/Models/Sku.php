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
        // LA LEY: Eliminados base_price, conversion_factor y weight del cast 
        // para que los Accessors tengan el control absoluto.
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function prices(): HasMany { return $this->hasMany(Price::class); }
    public function inventoryLots(): HasMany { return $this->hasMany(InventoryLot::class); }
    
    public function currentPrices(): HasMany
    {
        return $this->prices()
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            })
            // CORRECCIÓN CRÍTICA: Prioridad más alta (6) gana sobre la más baja (1)
            ->orderBy('priority', 'desc') 
            ->orderBy('min_quantity', 'desc');
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

                return $price ? $price->final_price : $this->base_price;
            }
        );
    }

    // =================================================================================
    // ACCESSORS SENIOR (Tolerancia Cero a Errores de Formato)
    // =================================================================================

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public static function getAvailableForBundles()
    {
        return self::with('product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'product_id', 'name', 'base_price']); 
    }

    public function getBasePriceAttribute($value): float
    {
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (float) $clean : 0.00;
    }

    public function getWeightAttribute($value): float
    {
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (float) $clean : 0.00;
    }

    public function getConversionFactorAttribute($value): float
    {
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (float) $clean : 1.00;
    }
}