<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class Brand extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'parent_id', 'provider_id', 'category_id', 'name', 'slug', 'bg_color',
        'image_path', 'website', 'is_active', 'is_featured', 'sort_order', 'description', 'deleted_epoch'
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'sort_order'    => 'integer',
        'deleted_epoch' => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function products(): HasMany { return $this->hasMany(Product::class)->where('deleted_epoch', 0); }
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class)->where('deleted_epoch', 0); }
    public function subBrands(): HasMany { return $this->hasMany(self::class, 'parent_id')->where('deleted_epoch', 0); }

    public function marketZones(): BelongsToMany 
    { 
        return $this->belongsToMany(MarketZone::class, 'brand_market_zone'); 
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
    }

    /**
     * Scope molecular para validar existencias físicas netas por sucursal.
     */
    public function scopeWhereHasStockInBranch($query, string $branchId)
    {
        return $query->whereHas('products', function ($q) use ($branchId) {
            $q->where('deleted_epoch', 0)
              ->where('is_active', true)
              ->whereHas('skus', function ($q) use ($branchId) {
                  $q->where('deleted_epoch', 0)
                    ->where('is_active', true)
                    ->whereHas('inventoryLots', function ($sub) use ($branchId) {
                        $sub->where('branch_id', $branchId)
                            ->whereRaw('(quantity - reserved_quantity) > 0');
                    });
              });
        });
    }

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }
}