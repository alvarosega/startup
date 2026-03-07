<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name', 'slug', 'external_code', 'tax_classification',
        'requires_age_check', 'is_active', 'is_featured', 'sort_order',
        'image_path', 'icon_path', 'bg_color', 'description',
        'seo_title', 'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requires_age_check' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $hidden = ['deleted_at'];

    // =================================================================================
    // RELACIONES (SOLO HACIA ABAJO)
    // =================================================================================

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // =================================================================================
    // LÓGICA DE CONSULTA (ENCAPSULACIÓN)
    // =================================================================================

    public static function getAllForAdmin(array $filters = [])
    {
        return self::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->get();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}