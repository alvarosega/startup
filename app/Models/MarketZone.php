<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MarketZone extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'hex_color',
        'svg_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'deleted_at', // Regla 3: Zero-Trust Leakage
    ];

    // =========================================================================
    // RELACIONES (NUEVA JERARQUÍA)
    // =========================================================================

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    // =========================================================================
    // SCOPES & OPTIMIZACIÓN
    // =========================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function getMinimalList()
    {
        return self::active()
            ->orderBy('name')
            ->get(['id', 'name', 'hex_color']); // Solo carga lo necesario
    }
}