<?php

declare(strict_types=1);

namespace App\Actions\Customer\Shop;

use App\Models\{MarketZone, Sku};
use Illuminate\Support\Facades\{Cache, DB};

class GetHomeZonesAction
{
    public function execute(string $branchId): array
    {
        $cacheKey = "home_zones_v2_br_{$branchId}";

        return Cache::remember($cacheKey, 300, function () use ($branchId) {
            // 1. Obtenemos las zonas activas
            $zones = MarketZone::query()
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            return $zones->map(function ($zone) use ($branchId) {
                // 2. Obtenemos IDs de marcas vinculadas a esta zona
                $brandIds = DB::table('brand_market_zone')
                    ->where('market_zone_id', $zone->id)
                    ->pluck('brand_id');

                // 3. Buscamos SKUs cuyos productos pertenezcan a esas marcas
                $skus = Sku::query()
                    ->where('skus.is_active', true)
                    ->whereHas('product', fn($q) => $q->whereIn('brand_id', $brandIds))
                    ->select('skus.*')
                    ->addSelect([
                        'available_stock' => DB::table('inventory_lots')
                            ->selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                            ->whereColumn('sku_id', 'skus.id')
                            ->where('branch_id', $branchId)
                            ->where('is_safety_stock', false)
                    ])
                    ->with(['product.brand', 'prices' => fn($q) => $q->where('branch_id', $branchId)])
                    ->limit(12)
                    ->get();

                return [
                    'id'   => $zone->id,
                    'name' => $zone->name,
                    'slug' => $zone->slug,
                    'skus' => $skus
                ];
            })->toArray();
        });
    }
}