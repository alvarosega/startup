<?php

namespace App\Actions\Customer\Shop;

use App\Models\Product;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\Auth;

class GetCategoryProductsAction
{
    public function __construct(
        protected GetShopSkusAction $getSkusAction,
        protected PriceResolverService $priceResolver
    ) {}

    /**
     * Obtiene los productos de una categoría con sus precios resueltos 
     * para una sucursal específica.
     */
    public function execute(string $categoryId, string $branchId): array
    {
        // 1. Obtener productos activos de la categoría con agregados de reseñas
        $productsData = Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when(Auth::guard('customer')->id(), function ($query, $cid) {
                $query->withExists(['favoritedBy as is_favorited' => fn($q) => $q->where('customer_id', $cid)]);
            })
            ->get()
            ->keyBy('id');

        if ($productsData->isEmpty()) {
            return [];
        }

        // 2. Obtener SKUs (Stock y Precios base) delegando a la acción de SKUs existente
        $skus = $this->getSkusAction->execute($productsData->keys()->toArray());

        // 3. Transformar y resolver precios dinámicos (Tiers)
        return $skus->groupBy('product_id')->map(function ($skusForProd) use ($productsData, $branchId) {
            $productId = $skusForProd->first()->product_id;
            $product = $productsData->get($productId);
            
            $resolvedSkus = $skusForProd->map(function($s) use ($branchId) {
                // Resolvemos el precio ganador para cantidad 1 (Precio base actual)
                $initialPrice = $this->priceResolver->resolveWinningPrice($s, $branchId, 1);
                
                return [
                    'id'              => (string) $s->id,
                    'name'            => (string) $s->name,
                    'image_url'       => (string) ($s->image_url ?? $s->image_path),
                    'available_stock' => (int) $s->available_stock,
                    // Enviamos todos los precios para que el componente Vue maneje los Tiers
                    'all_prices'      => \App\Models\Price::where('sku_id', $s->id)
                                            ->where('branch_id', $branchId)
                                            ->get(),
                    'price'           => (float) $initialPrice->final_price,
                    'list_price'      => (float) $initialPrice->list_price,
                ];
            })->values();

            return [
                'id'                 => (string) $product->id,
                'name'               => (string) $product->name,
                'image_url'          => (string) ($product->image_path ?? $resolvedSkus->first()['image_url']),
                'min_price'          => (float) $resolvedSkus->min('price'),
                'reviews_avg_rating' => (float) ($product->reviews_avg_rating ?? 0),
                'reviews_count'      => (int) ($product->reviews_count ?? 0),
                'is_favorited'       => (bool) ($product->is_favorited ?? false),
                'skus'               => $resolvedSkus
            ];
        })->values()->toArray();
    }
}