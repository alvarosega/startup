<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\HasUuidv7; // <--- Trait UUID
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Category extends Model
{
    use HasFactory, SoftDeletes, HasUuidv7;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'external_code',
        'tax_classification', 'requires_age_check',
        'seo_title', 'seo_description', 'description',
        'image_path', 'icon_path', 'bg_color',
        'is_active', 'is_featured', 'sort_order'
    ];
    protected $appends = ['image_url']; 

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requires_age_check' => 'boolean',
        'sort_order' => 'integer',
    ];

    

    public function getImageUrlAttribute()
    {
        return $this->image_path 
            ? Storage::url($this->image_path) 
            : null;
    }

    // Scopes
    public function scopeRoots($query) { return $query->whereNull('parent_id'); }
    public function scopeActive($query) { return $query->where('is_active', true); }

    // Relaciones
    public function parent() { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children() { return $this->hasMany(Category::class, 'parent_id'); }
    public function zone() {
        return $this->belongsTo(MarketZone::class, 'market_zone_id');
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relación: Una categoría padre tiene muchas subcategorías (hijos).
     * (Probablemente ya tengas esto, pero lo dejo por referencia)
     */

}