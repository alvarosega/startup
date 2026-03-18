<?php

namespace App\Actions\Customer\Shop;

use App\Models\{MarketZone, Product, Brand, Price};
use Illuminate\Support\Facades\Auth;
use App\Services\Finance\PriceResolverService;

class GetShopZoneAction
{
    public function __construct(
        protected GetShopSkusAction $getSkusAction,
        protected PriceResolverService $priceResolver
    ) {}


    public function execute(MarketZone $zone, string $branchId, ?string $onlyBrandId = null): array
    {
        // Navegación: Solo marcas que REALMENTE tienen stock ahora mismo
        $brands = Brand::where('market_zone_id', $zone->id)
            ->whereHasStockInBranch($branchId)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        if (!$onlyBrandId) {
            return [
                'zone' => $zone,
                'brandsNavigation' => $brands,
                'brandContent' => null
            ];
        }

        // Carga de contenido: Solo para la marca solicitada
        $brand = Brand::whereHasStockInBranch($branchId)->findOrFail($onlyBrandId);
        
        // Obtenemos SKUs con stock y sus precios usando la relación que definiste
        $products = Product::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->with(['category', 'skus' => function($q) use ($branchId) {
                $q->where('is_active', true)
                ->addSelect([
                    'available_stock' => \App\Models\InventoryLot::selectRaw('COALESCE(SUM(quantity - reserved_quantity), 0)')
                        ->whereColumn('sku_id', 'skus.id')
                        ->where('branch_id', $branchId)
                        ->where('is_safety_stock', false)
                ])
                ->with(['prices' => function($pq) use ($branchId) {
                    $pq->where('branch_id', $branchId)
                        ->where('valid_from', '<=', now())
                        ->where(fn($i) => $i->whereNull('valid_to')->orWhere('valid_to', '>=', now()))
                        ->orderBy('priority', 'desc');
                }]);
            }])
            ->get();

        // Formateamos para que Vue no tenga que calcular nada pesado
        $brandContent = [
            'id' => $brand->id,
            'categories' => $products->groupBy('category_id')->map(function($prods) {
                $cat = $prods->first()->category;
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'bg_color' => $cat->bg_color,
                    'products' => $prods->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image_url' => $p->image_path,
                        'available_stock' => $p->skus->sum('available_stock'),
                        'min_price' => (float) $p->skus->min('base_price'),
                        'skus' => $p->skus->map(fn($s) => [
                            'id' => $s->id,
                            'name' => $s->name,
                            'price' => (float) $s->base_price,
                            'image_url' => $s->image_path,
                            'available_stock' => (int) $s->available_stock,
                            'all_prices' => $s->prices
                        ])
                    ])->filter(fn($p) => $p['available_stock'] > 0)->values()
                ];
            })->values()
        ];

        return [
            'zone' => $zone,
            'brandsNavigation' => $brands,
            'brandContent' => $brandContent
        ];
    }
}