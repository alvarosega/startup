<?php

namespace App\Actions\Admin\MarketZone;

use App\Models\MarketZone;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Cache}; // Asegura tener Cache

class DeleteMarketZoneAction
{
    public function execute(MarketZone $zone): bool
    {
        return DB::transaction(function () use ($zone) {
            
            if ($zone->brands()->exists()) {
                throw ValidationException::withMessages([
                    'zone' => 'Vulneración de integridad: Existen marcas asociadas a esta zona de mercado.'
                ]);
            }

            $deleted = $zone->delete();

            // CORRECCIÓN: Invalidación por llave directa
            Cache::forget('market_zones_admin_list');

            return $deleted;
        });
    }
}