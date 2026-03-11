<?php

namespace App\Actions\Customer\Shop;

use App\Models\MarketZone;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Services\Finance\PriceResolverService;

class GetShopZoneAction
{
    public function __construct(
        protected GetShopSkusAction $getSkusAction,
        protected PriceResolverService $priceResolver
    ) {}

    public function execute(MarketZone $zone, string $branchId): array
    {
        // 1. Obtener Marcas activas de la zona (Ordenadas)
        $brands = Brand::where('market_zone_id', $zone->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $brandIds = $brands->pluck('id')->toArray();

        // 2. Obtener los productos con sus agregados de rendimiento (REGLA 3.A)
        // Usamos direct query con 'with' para evitar el error de Collection::loadAvg
        $customerId = Auth::guard('customer')->id();

        $productsData = Product::whereIn('brand_id', $brandIds)
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($customerId, function ($query) use ($customerId) {
                $query->withExists(['favoritedBy as is_favorited' => function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId);
                }]);
            })
            ->with(['brand', 'category']) // Eager loading para la agrupación
            ->get()
            ->keyBy('id'); // Indexamos por ID para cruce rápido con SKUs

        // 3. Obtener SKUs (Stock y Precios por Sucursal)
        $skus = $this->getSkusAction->execute($productsData->keys()->toArray());

// 4. AGRUPACIÓN COMPLEJA V3.0: Brand -> Category (Color) -> Product -> SKUs
        // IMPORTANTE: Añadimos $branchId al primer 'use'
        $structuredData = $brands->map(function ($brand) use ($productsData, $skus, $branchId) {
            
            $skusInBrand = $skus->filter(fn($sku) => $sku->product->brand_id === $brand->id);
            if ($skusInBrand->isEmpty()) return null;

            return [
                'id'         => (string) $brand->id,
                'name'       => (string) $brand->name,
                // Pasamos $branchId al siguiente nivel
                'categories' => $skusInBrand->groupBy('product.category_id')->map(function ($skusInCat) use ($productsData, $branchId) {
                    $firstSku  = $skusInCat->first();
                    $category  = $firstSku->product->category;
                    
                    return [
                        'id'       => (string) ($category->id ?? 'uncategorized'),
                        'name'     => (string) ($category->name ?? 'Otros'),
                        'bg_color' => (string) ($category->bg_color ?? '#FFFFFF'),
                        // Pasamos $branchId al nivel de productos
                        'products' => $skusInCat->groupBy('product_id')->map(function ($skusForProd) use ($productsData, $branchId) {
                            $productId = $skusForProd->first()->product_id;
                            $product   = $productsData->get($productId);
                            $totalStock = $skusForProd->sum('available_stock');

                            // Resolvemos los precios de todos los SKUs (Aquí es donde se usaba $branchId)
                            // ... dentro del map de skusForProd ...
                            $resolvedSkus = $skusForProd->map(function($s) use ($branchId) {
                                // Obtenemos TODOS los precios válidos para este SKU en esta sucursal
                                $allAvailablePrices = \App\Models\Price::where('sku_id', $s->id)
                                    ->where('branch_id', $branchId)
                                    ->where('valid_from', '<=', now())
                                    ->where(function ($q) {
                                        $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
                                    })
                                    ->orderBy('priority', 'desc')
                                    ->orderBy('min_quantity', 'desc')
                                    ->get()
                                    ->map(fn($p) => [
                                        'type'         => (string) $p->type,
                                        'final_price'  => (float) $p->final_price,
                                        'list_price'   => (float) $p->list_price,
                                        'min_quantity' => (int) $p->min_quantity,
                                        'priority'     => (int) $p->priority,
                                    ]);

                                // El precio inicial (para cantidad 1) sigue siendo el ganador por defecto
                                $initialPrice = $this->priceResolver->resolveWinningPrice($s, $branchId, 1);
                                
                                return [
                                    'id'              => (string) $s->id,
                                    'name'            => (string) $s->name,
                                    'image_url'       => (string) ($s->image_url ?? $s->image_path),
                                    'available_stock' => (int) $s->available_stock,
                                    'stock_status'    => (string) $s->stock_status,
                                    
                                    // --- NUEVA LÓGICA: Array de precios para resolución en el Cliente ---
                                    'all_prices'      => $allAvailablePrices, 
                                    
                                    // Datos iniciales para la grilla
                                    'price'           => (float) $initialPrice->final_price,
                                    'list_price'      => (float) $initialPrice->list_price,
                                    'next_tier'       => $initialPrice->next_tier ? [
                                        'min_qty' => (int) $initialPrice->next_tier->min_quantity,
                                        'price'   => (float) $initialPrice->next_tier->final_price
                                    ] : null
                                ];
                            })->values();

                            $minPriceSku = $resolvedSkus->sortBy('price')->first();

                            return [
                                'id'                 => (string) $product->id,
                                'name'               => (string) $product->name,
                                'image_url'          => (string) ($product->image_path ?? $skusForProd->first()->image_url),
                                'available_stock'    => (int) $totalStock,
                                'stock_status'       => $totalStock > 0 ? 'in_stock' : 'out_of_stock',
                                'min_price'          => (float) ($minPriceSku['price'] ?? 0),
                                'list_price'         => (float) ($minPriceSku['list_price'] ?? 0),
                                'reviews_avg_rating' => (float) ($product->reviews_avg_rating ?? 0),
                                'reviews_count'      => (int) ($product->reviews_count ?? 0),
                                'is_favorited'       => (bool) ($product->is_favorited ?? false),
                                'skus'               => $resolvedSkus
                            ];
                        })->values()
                    ];
                })->values()
            ];
        })->filter()->values();

        return [
            'zone'              => $zone,
            'groupedCategories' => $structuredData
        ];
    }
}