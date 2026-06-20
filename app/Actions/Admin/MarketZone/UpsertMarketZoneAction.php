<?php

namespace App\Actions\Admin\MarketZone;

use App\Models\MarketZone;
use App\DTOs\Admin\MarketZone\MarketZoneData;
use Illuminate\Support\Facades\Cache;

class UpsertMarketZoneAction
{
    public function execute(MarketZoneData $data, ?MarketZone $zone = null): MarketZone
    {
        $attributes = [
            'name'        => $data->name,
            'slug'        => $data->slug,
            'hex_color'   => $data->hex_color,
            'svg_id'      => $data->svg_id,
            'description' => $data->description,
            'is_active'   => $data->is_active,
        ];

        if ($zone) {
            $zone->update($attributes);
        } else {
            $zone = MarketZone::create($attributes);
        }

        // Romper la caché estática que definiste en MarketZoneController
        Cache::forget('market_zones_admin_list');

        return $zone;
    }
}