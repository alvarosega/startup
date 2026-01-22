<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id', 'name', 'slug', 'manufacturer', 
        'origin_country_code', 'image_path', 'website', 'tier',
        'is_featured', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean', 
        'is_featured' => 'boolean'
    ];
    
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path 
            ? Storage::url($this->image_path) 
            : null; // Frontend manejará el placeholder
    }

    // --- RELACIONES ---
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category');
    }
}