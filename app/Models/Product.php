<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;


class Product extends Model
{
    use HasFactory, SoftDeletes;

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
    
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}