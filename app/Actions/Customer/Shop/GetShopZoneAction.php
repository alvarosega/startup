<?php

namespace App\Actions\Customer\Shop;

use App\Models\MarketZone;
use App\Models\Product;

class GetShopZoneAction
{
    public function __construct(
        protected GetShopSkusAction $getSkusAction
    ) {}

    public function execute(MarketZone $zone, string $branchId): array
    {
        // 1. Carga la jerarquía de categorías
        $zone->load(['aisles.children']);

        // 2. Recolecta IDs de todas las categorías (Padres e Hijas)
        $categoryIds = $zone->aisles->flatMap(function ($aisle) {
            return collect([$aisle->id])->concat($aisle->children->pluck('id'));
        })->unique()->toArray();

        // 3. Obtiene solo los productos que pertenecen a estas categorías
        $productIds = Product::whereIn('category_id', $categoryIds)->pluck('id')->toArray();

        // 4. Obtiene los SKUs con el stock y precios ya filtrados por la sub-action
        $skus = $this->getSkusAction->execute($productIds);

        // 5. Agrupación Jerárquica: Pasillo -> Subcategoría -> SKU
        $structuredData = $skus->groupBy(function ($sku) {
            // Agrupar por el Pasillo (Padre)
            return $sku->product->category->parent_id ?? $sku->product->category_id;
        })->map(function ($skusInAisle) {
            $first = $skusInAisle->first();
            $aisle = $first->product->category->parent ?? $first->product->category;

            return [
                'id'   => $aisle->id,
                'name' => $aisle->name,
                'subcategories' => $skusInAisle->groupBy('product.category_id')
                    ->map(function ($skusInSub) {
                        $sub = $skusInSub->first()->product->category;
                        return [
                            'id'       => $sub->id,
                            'name'     => $sub->name,
                            'products' => $skusInSub->map(fn($sku) => [
                                'id'              => $sku->id,
                                'name'            => "{$sku->product->name} {$sku->name}",
                                'price'           => $sku->display_price,
                                'image_url'       => $sku->image_url ?? $sku->product->image_url,
                                'available_stock' => (int) $sku->available_stock, // Forzar entero
                                'stock_status'    => $sku->stock_status, // 'in_stock' o 'out_of_stock'
                                'variants'        => [$sku], 
                            ])->values()
                        ];
                    })->values()
            ];
        })->values();

        return [
            'zone'              => $zone,
            'groupedCategories' => $structuredData
        ];
    }
}