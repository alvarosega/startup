<?php

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Category extends Model
{
    use SoftDeletes, HasUv7;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'external_code', 'tax_classification',
        'requires_age_check', 'is_active', 'is_featured', 'sort_order',
        'image_path', 'icon_path', 'bg_color', 'description', 'version'
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean', // Faltaba este
        'requires_age_check' => 'boolean',
        'sort_order'         => 'integer',
        'version' => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function brands(): HasMany { return $this->hasMany(Brand::class); }
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany 
    { 
        return $this->hasMany(Product::class); 
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    protected static function booted()
    {
        static::updating(function ($model) {
            $model->version++;
        });
    }
}