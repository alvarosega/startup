<?php

namespace App\Actions\Customer\Shop;

use App\Models\MarketZone;
use App\Models\Bundle;
use App\Http\Resources\Customer\Shop\ShopBrandResource;
use Carbon\Carbon;

class GetShopLandingAction
{
    public function execute(string $branchId): array
    {
        // 1. Obtener Zonas con sus marcas (Carga atómica)
        $zonesData = MarketZone::where('is_active', true)
            ->with(['brands' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->get()
            ->map(function ($zone) {
                // Si la zona no tiene marcas, no tiene "cobertura" visual
                if ($zone->brands->isEmpty()) return null;

                return [
                    'slug'   => (string) $zone->slug,
                    'svg_id' => (string) $zone->svg_id,
                    'name'   => (string) $zone->name,
                    'color'  => (string) $zone->hex_color,
                    // Mapeamos marcas a 'aisles' para compatibilidad con el componente Vue
                    'aisles' => ShopBrandResource::collection($zone->brands)->resolve()
                ];
            })
            ->filter()
            ->keyBy('slug');

        $bundles = Bundle::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->with(['items']) 
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', Carbon::now());
            })
            ->get();

        return [
            'zones'   => $zonesData,
            'bundles' => $bundles
        ];
    }
}