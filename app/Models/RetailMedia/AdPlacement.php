<?php

declare(strict_types=1);

namespace App\Models\RetailMedia;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    use HasFactory, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'max_items',
        'is_active'
    ];

    protected $casts = [
        'max_items' => 'integer',
        'is_active' => 'boolean'
    ];

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class, 'placement_id');
    }
}