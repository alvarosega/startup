<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo, HasManyThrough};
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'brand_id', 'category_id', 'name', 'slug', 'description', 
        'image_path','sort_order', 'is_active', 'is_alcoholic'
    ];
    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        });
    }
    protected $casts = [
        'is_active' => 'boolean',
        'is_alcoholic' => 'boolean'
    ];

    public function skus(): HasMany { return $this->hasMany(Sku::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function prices(): HasManyThrough {
        return $this->hasManyThrough(Price::class, Sku::class, 'product_id', 'sku_id');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'favorites', 'product_id', 'customer_id');
    }
}