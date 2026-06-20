<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Category extends Model
{
    use SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'parent_id', 'name', 'slug', 'external_code', 'tax_classification',
        'requires_age_check', 'is_active', 'is_featured', 'sort_order',
        'image_path', 'icon_path', 'bg_color', 'description', 'deleted_epoch'
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
        'requires_age_check' => 'boolean',
        'sort_order'         => 'integer',
        'deleted_epoch'      => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id')->where('deleted_epoch', 0); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->where('deleted_epoch', 0)->orderBy('sort_order'); }
    public function brands(): HasMany { return $this->hasMany(Brand::class)->where('deleted_epoch', 0); }
    public function products(): HasMany { return $this->hasMany(Product::class)->where('deleted_epoch', 0); }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
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