<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'parent_id',
        'market_zone_id',
        'name',
        'slug',
        'external_code',
        'tax_classification',
        'requires_age_check',
        'is_active',
        'is_featured',
        'sort_order',
        'image_path',
        'icon_path',
        'bg_color',
        'description',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requires_age_check' => 'boolean',
        'sort_order' => 'integer',
    ];
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
    // =================================================================================
    // RELACIONES
    // =================================================================================

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}