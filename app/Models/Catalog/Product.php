<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'brand_id', 'category_id', 'name', 'slug', 'description', 
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
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            // RECTIFICACIÓN: Mutación silenciosa para impedir re-ejecución en cadena de eventos de guardado
            $model->saveQuietly();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function skus(): HasMany 
    { 
        return $this->hasMany(Sku::class); 
    }

    public function brand(): BelongsTo 
    { 
        return $this->belongsTo(Brand::class); 
    }

    public function category(): BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }

    public function prices(): HasManyThrough 
    {
        return $this->hasManyThrough(\App\Models\Price::class, Sku::class, 'product_id', 'sku_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}