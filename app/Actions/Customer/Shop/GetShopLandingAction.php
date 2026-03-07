<?php

namespace App\Actions\Customer\Shop;

use App\Models\MarketZone;
use App\Models\Bundle;
use App\Http\Resources\Customer\Shop\ShopCategoryResource;
use App\Http\Resources\Customer\Shop\ShopProductResource;
use Carbon\Carbon;

class GetShopLandingAction
{
    public function execute(string $branchId): array
    {
        // 1. Obtener Zonas con sus categorías raíz (Eager Loading para evitar N+1)
        $zonesData = MarketZone::with(['categories' => function ($q) {
            $q->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order');
        }])->get()->map(function ($zone) {
            if ($zone->categories->isEmpty()) return null;

            return [
                'slug'   => $zone->slug,
                'svg_id' => $zone->svg_id,
                'name'   => $zone->name,
                'color'  => $zone->hex_color,
                'aisles' => ShopCategoryResource::collection($zone->categories)->resolve()
            ];
        })->filter()->keyBy('slug');

        $bundles = Bundle::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->with(['items']) // <- Modificado: Carga directamente los SKUs
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', Carbon::now());
            })
            ->get();

        return [
            'zones'   => $zonesData,
            'bundles' => $bundles
        ];
    }
}