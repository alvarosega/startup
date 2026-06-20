<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    use HasUuids;

    protected $table = 'ad_placements';

    protected $fillable = [
        'name',
        'code',
        'max_items',
        'is_active',
    ];

    protected $casts = [
        'max_items' => 'integer',
        'is_active' => 'boolean',
    ];

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class, 'placement_id');
    }
}