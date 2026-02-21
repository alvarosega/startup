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
        'image_path', 'is_active', 'is_alcoholic'
    ];

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
}