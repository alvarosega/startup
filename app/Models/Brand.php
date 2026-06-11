<?php

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Support\Facades\DB; 

class Brand extends Model
{
    use SoftDeletes, HasUv7;

    protected $fillable = [
        'parent_id', 'provider_id', 'category_id', 'name', 'slug', 'bg_color',
        'image_path', 'website', 'is_active', 'is_featured', 'sort_order', 'description', 'deleted_epoch'
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'sort_order'    => 'integer',
        'deleted_epoch' => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function provider(): BelongsTo 
    { 
        return $this->belongsTo(Provider::class); 
    }

    public function marketZones(): BelongsToMany 
    { 
        return $this->belongsToMany(MarketZone::class, 'brand_market_zone'); 
    }
    
    public function category(): BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }
    
    public function subBrands(): HasMany 
    { 
        return $this->hasMany(self::class, 'parent_id'); 
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            $model->save();
        });

        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }
}