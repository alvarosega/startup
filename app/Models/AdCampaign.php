<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdCampaign extends Model {
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['provider_id', 'market_zone_id', 'name', 'type', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function provider(): BelongsTo 
    {
        return $this->belongsTo(Provider::class);
    }

    public function creatives(): HasMany 
    { 
        return $this->hasMany(AdCreative::class, 'campaign_id'); 
    }

    public function marketZone(): BelongsTo 
    { 
        return $this->belongsTo(MarketZone::class); 
    }
}