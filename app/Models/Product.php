<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\InventoryLot;
use App\Models\Concerns\HasUuidv7;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUuidv7;

    protected $fillable = [
        'brand_id', 'category_id', 'name', 'slug',
        'description', 'image_path', 'is_active', 'is_alcoholic'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_alcoholic' => 'boolean'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    
    public function category() { return $this->belongsTo(Category::class); }

    use SoftDeletes;
    protected $guarded = [];

    public function skus()
    {
        return $this->hasMany(Sku::class);
    }
    

    public function inventoryLots()
    {
        return $this->hasManyThrough(
            InventoryLot::class, // Modelo final (Lotes)
            Sku::class,          // Modelo intermedio (SKUs)
            'product_id',        // FK en tabla SKUs
            'sku_id',            // FK en tabla InventoryLots
            'id',                // PK en tabla Products
            'id'                 // PK en tabla SKUs
        );
    }
    /**
     * Un producto pertenece a una marca.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }


}