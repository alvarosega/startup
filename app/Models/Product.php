<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo, HasManyThrough, BelongsToMany};

class Product extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'brand_id', 'category_id', 'name', 'slug', 'description', 
        'image_path', 'sort_order', 'is_active', 'is_featured', 'is_alcoholic', 'deleted_epoch'
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'is_alcoholic'  => 'boolean',
        'sort_order'    => 'integer',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        // LEY: Se suprime el GlobalScope order para preservar rendimiento del FULLTEXT INDEX.
        
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function skus(): HasMany { return $this->hasMany(Sku::class)->where('deleted_epoch', 0); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class)->where('deleted_epoch', 0); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class)->where('deleted_epoch', 0); }

    public function prices(): HasManyThrough 
    {
        return $this->hasManyThrough(Price::class, Sku::class, 'product_id', 'sku_id')
            ->where('prices.deleted_epoch', 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
    }
}