<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\HasBinaryUuid;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'brand_id', 
        'category_id', 
        'name', 
        'slug',
        'description', 
        'image_path', 
        'is_active', 
        'is_alcoholic'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_alcoholic' => 'boolean'
    ];

    protected $appends = ['image_url'];

    // Accessor para la URL de la imagen
    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    // Relación con SKUs
    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class);
    }

    // Relación con Brand (brands.id es bigint)
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    // Relación con Category (categories.id es UUID)
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    // Relación con InventoryLots a través de SKUs
    public function inventoryLots(): HasManyThrough
    {
        return $this->hasManyThrough(
            InventoryLot::class,  // Modelo final
            Sku::class,           // Modelo intermedio
            'product_id',         // FK en SKUs que apunta a Products
            'sku_id',             // FK en InventoryLots que apunta a SKUs
            'id',                 // PK en Products
            'id'                  // PK en SKUs
        );
    }
    public function prices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Price::class,
            Sku::class,
            'product_id', // FK en SKUs
            'sku_id',     // FK en Prices
            'id',         // PK en Products
            'id'          // PK en SKUs
        );
    }
}