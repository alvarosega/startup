<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUv7;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MarketZone extends Model
{
    use SoftDeletes, HasUv7;

    protected $fillable = ['name', 'slug', 'hex_color', 'svg_id', 'description', 'sort_order', 'is_active'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function brands(): BelongsToMany { 
        return $this->belongsToMany(Brand::class, 'brand_market_zone'); 
    }

    public static function getMinimalList()
    {
        return Cache::remember('market_zones_minimal_v1', 86400, function () {
            return self::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name']);
        });
    }
}