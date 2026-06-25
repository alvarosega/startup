<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'parent_id', 'name', 'slug', 'external_code', 'tax_classification',
        'requires_age_check', 'is_active', 'is_featured', 'sort_order',
        'image_path', 'icon_path', 'bg_color', 'description', 'deleted_epoch',
        'seo_title', 'seo_description'
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
        'requires_age_check' => 'boolean',
        'sort_order'         => 'integer',
        'deleted_epoch'      => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            $model->deleted_epoch = time();
            // Evita la activación colateral de hooks intermedios de actualización
            $model->saveQuietly();
        });
    
        static::restoring(function (self $model) {
            $model->deleted_epoch = 0;
        });
    }

    public function parent(): BelongsTo 
    { 
        return $this->belongsTo(self::class, 'parent_id'); 
    }

    public function children(): HasMany 
    { 
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); 
    }

    public function brands(): HasMany 
    { 
        return $this->hasMany(Brand::class); 
    }

    public function products(): HasMany 
    { 
        return $this->hasMany(Product::class); 
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}