<?php

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes, CachesAttributes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use SoftDeletes, HasUv7;
    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];
    protected $fillable = [
        'parent_id', 'provider_id', 'category_id', 'name', 'slug', 
        'image_path', 'website', 'is_active', 'is_featured', 'sort_order', 'description'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    // PHP 8.2+ Attribute Pattern
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path 
            ? Storage::disk('public')->url($this->image_path) 
            : null);
    }
    public function provider(): BelongsTo { 
        return $this->belongsTo(Provider::class); 
    }
    public function marketZones(): BelongsToMany { 
        return $this->belongsToMany(MarketZone::class, 'brand_market_zone'); 
    }
    
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subBrands(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
}