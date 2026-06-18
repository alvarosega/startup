<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bundle extends Model
{
    use HasUuids;

    protected $table = 'bundles';

    protected $fillable = [
        'name',
        'image_path',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'type'      => 'string',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }

    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'bundle_items', 'bundle_id', 'sku_id')
                    ->withTimestamps();
    }
}