<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'provider_id', 'category_id', 'market_zone_id',
        'name', 'slug', 'image_path', 'website', 'is_active', 
        'is_featured', 'sort_order', 'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $hidden = ['deleted_at'];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function provider(): BelongsTo { return $this->belongsTo(Provider::class); }


    // =========================================================================
    // ACCESORES (SANITIZACIÓN)
    // =========================================================================

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function marketZone(): \Illuminate\Database\Eloquent\Relations\BelongsTo 
    { 
        return $this->belongsTo(MarketZone::class); 
    }
    
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany 
    { 
        return $this->hasMany(Product::class); 
    }
    
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }
}