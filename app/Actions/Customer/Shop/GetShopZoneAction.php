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

        // 5. Agrupación Jerárquica REAL: Pasillo -> Subcategoría -> Producto -> SKUs
        $structuredData = $skus->groupBy(function ($sku) {
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
                            // AGRUPACIÓN POR PRODUCTO
                            'products' => $skusInSub->groupBy('product_id')->map(function ($skusForProduct) {
                                $product = $skusForProduct->first()->product;
                                
                                // Calculamos el stock total del producto sumando sus SKUs
                                $totalStock = $skusForProduct->sum('available_stock');
                                
                                return [
                                    'id'              => $product->id,
                                    'name'            => $product->name, // Nombre limpio del producto
                                    'image_url'       => $product->image_path ?? $skusForProduct->first()->image_url,
                                    'available_stock' => (int) $totalStock,
                                    'stock_status'    => $totalStock > 0 ? 'in_stock' : 'out_of_stock',
                                    'min_price'       => $skusForProduct->min('display_price'), // Precio "Desde X"
                                    
                                    // Los SKUs reales anidados para el Bottom Sheet
                                    'skus'            => $skusForProduct->map(fn($sku) => [
                                        'id'              => $sku->id,
                                        'name'            => $sku->name, // Ej: "1 Litro"
                                        'price'           => $sku->display_price,
                                        'image_url'       => $sku->image_url ?? $sku->image_path,
                                        'available_stock' => (int) $sku->available_stock,
                                        'stock_status'    => $sku->stock_status,
                                    ])->values()
                                ];
                            })->values()
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