<?php

namespace App\Actions\Customer\Shop;

use App\Models\{MarketZone, Bundle, Category};
use App\DTOs\Customer\Shop\LandingQueryDTO;
use App\Http\Resources\Customer\Shop\{LandingCategoryResource, ShopBrandResource, ShopBundleResource};
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GetShopLandingAction
{
    public function execute(LandingQueryDTO $dto): array
    {
        $now = Carbon::now();

        // Regla 3.A: Caché atómica segmentada por sucursal
        return Cache::remember("shop_landing_{$dto->branchId}_v3", 3600, function () use ($dto, $now) {
            
            // 1. Market Zones & Marcas (Sanitización High-Performance)
            $zones = MarketZone::where('is_active', true)
                ->with(['brands' => function ($q) use ($dto) {
                    $q->active()
                    ->whereHasStockInBranch($dto->branchId) // Usamos tu scope del modelo Brand
                    ->orderBy('sort_order');
                }])
                ->get()
                ->map(function ($zone) {
                    if ($zone->brands->isEmpty()) return null;

                    return [
                        'id'     => (string) $zone->id, // <--- AÑADIDO: Vital para el :key de Vue
                        'slug'   => (string) $zone->slug,
                        'name'   => $this->purify($zone->name),
                        'color'  => (string) ($zone->hex_color ?? '#007AFF'),
                        'svg_id' => (string) $zone->svg_id,
                        'aisles' => ShopBrandResource::collection($zone->brands)->resolve()
                    ];
                })->filter()->values();

            // 2. Categorías (Carrusel)
            $categories = Category::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->limit(30)
                ->get();
                $bundles = Bundle::where('branch_id', $dto->branchId)
                ->where('is_active', true)
                // Cargamos SKUs y sus lotes de stock de una sola vez (Eager Loading)
                ->with(['skus.inventoryLots' => function ($q) use ($dto) {
                    $q->where('branch_id', $dto->branchId)
                      ->where('is_safety_stock', false);
                }])
                ->where(function ($query) use ($now) {
                    $query->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                          ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now));
                })
                ->get()
                ->map(function ($bundle) {
                    $availability = [];
            
                    foreach ($bundle->skus as $sku) {
                        // Calculamos el stock desde la relación ya cargada en memoria (Cero queries extra)
                        $stock = $sku->inventoryLots->sum(fn($lot) => $lot->quantity - $lot->reserved_quantity);
                        
                        $requiredQty = $sku->pivot->quantity;
                        $availability[] = ($requiredQty > 0) ? floor($stock / $requiredQty) : 0;
                    }
            
                    $bundle->virtual_stock = empty($availability) ? 0 : (int) min($availability);
                    $bundle->stock_status = $bundle->virtual_stock > 0 ? 'in_stock' : 'out_of_stock';
            
                    return $bundle;
                });
            
            return [
                'zones'      => $zones, // Ya es un array por el map anterior
                'categories' => LandingCategoryResource::collection($categories)->resolve(),
                'bundles'    => ShopBundleResource::collection($bundles)->resolve()
            ];
        });
    }

    private function purify(?string $str): string {
        if (!$str) return '';
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}