<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Sku extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id', 'name', 'code', 'base_price', 
        'conversion_factor', 'weight', 'image_path',
        'sort_order', 'is_active', 'deleted_epoch'
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'sort_order'        => 'integer',
        'deleted_epoch'     => 'integer',
        'base_price'        => 'decimal:2',
        'weight'            => 'decimal:3',
        'conversion_factor' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::creating(function (Sku $sku) {
            if (empty($sku->code)) {
                $sku->code = self::generateInternalEan();
            }
        });

        static::updating(function (Sku $sku) {
            if ($sku->isDirty('code') && !empty($sku->getOriginal('code'))) {
                throw new \Exception("VIOLACIÓN_DE_PROTOCOLO: El código SKU es inmutable.");
            }
        });

        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    private static function generateInternalEan(): string
    {
        $base = '21' . substr(number_format(microtime(true) * 1000, 0, '', ''), -8) . sprintf('%02d', random_int(0, 99));
        $sum = 0;
        foreach (str_split($base) as $i => $digit) {
            $sum += (int)$digit * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $base . $checkDigit;
    }

    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class); 
    }

    public function prices(): HasMany 
    { 
        return $this->hasMany(\App\Models\Price::class); 
    }

    public function inventoryLots(): HasMany 
    { 
        return $this->hasMany(\App\Models\InventoryLot::class); 
    }

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Bundle::class, 'bundle_items')
                    ->withPivot('quantity')
                    ->withTimestamps()
                    ->using(\App\Models\BundleItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->image_path) {
                return asset('storage/' . $this->image_path);
            }
            return asset('assets/img/sku_placeholder.png');
        });
    }

    protected function basePrice(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? $value : 0.00,
            get: fn ($value) => (float) $value
        );
    }

    protected function weight(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? $value : 0.000,
            get: fn ($value) => (float) $value
        );
    }

    protected function conversionFactor(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_numeric($value) ? $value : 1.000,
            get: fn ($value) => (float) $value
        );
    }

    protected function availableStock(): Attribute
    {
        return Attribute::get(function () {
            if (array_key_exists('available_stock', $this->attributes)) {
                return max(0, (int) $this->attributes['available_stock']);
            }
            return max(0, ((int)($this->total_physical ?? 0)) - ((int)($this->total_reserved ?? 0)));
        });
    }

    protected function resolvedPrice(): Attribute
    {
        return Attribute::get(function () {
            $price = $this->relationLoaded('prices') ? $this->prices->first() : null;

            return (object) [
                'final_price' => $price ? (float) $price->final_price : (float) $this->base_price,
                'list_price'  => $price ? (float) $price->list_price : (float) $this->base_price,
                'next_tier'   => null
            ];
        });
    }

    public static function getAvailableForBundles()
    {
        return self::select(['id', 'product_id', 'name', 'base_price'])
            ->with('product:id,name')
            ->active()
            ->orderBy('name')
            ->get();
    }
}