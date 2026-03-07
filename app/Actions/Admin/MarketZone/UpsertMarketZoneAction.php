<?php

namespace App\Actions\Admin\MarketZone;

use App\Models\MarketZone;
use App\DTOs\Admin\MarketZone\MarketZoneData;
use Illuminate\Support\Facades\{DB, Cache};

class UpsertMarketZoneAction
{
    public function execute(MarketZoneData $data, ?MarketZone $zone = null): MarketZone
    {
        return DB::transaction(function () use ($data, $zone) {
            
            $attributes = $data->toArray();

            if (!$zone) {
                $zone = MarketZone::create($attributes);
            } else {
                $zone->update($attributes);
            }

            // CORRECCIÓN: Invalidación por llave directa
            Cache::forget('market_zones_admin_list');
            Cache::forget('market_zones_minimal_list'); // Por si lo usas en selects

            return $zone;
        });
    }
}