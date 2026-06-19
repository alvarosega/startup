<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AdCampaign extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ad_campaigns';

    protected $fillable = [
        'provider_id',
        'name',
        'type',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class, 'campaign_id');
    }

    /**
     * Scope para restringir la consulta a campañas vigentes temporalmente.
     */
    public function scopeScopeVigente(Builder $query): void
    {
        $now = now();
        $query->where('is_active', true)
              ->where(fn(Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
              ->where(fn(Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }
}