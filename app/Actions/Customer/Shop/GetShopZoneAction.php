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
        // 1. Extraemos los IDs de las marcas (Esto ya lo habíamos corregido)
        $brandIds = $zone->brands()->pluck('id')->toArray();

        $productIds = \App\Models\Product::whereIn('brand_id', $brandIds)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        // 3. Obtiene los SKUs
        $skus = $this->getSkusAction->execute($productIds);

        // 4. AGRUPACIÓN V2.0: Marca -> Categoría -> Producto -> SKUs
        $structuredData = $skus->groupBy('product.brand_id')->map(function ($skusInBrand) {
            $brand = $skusInBrand->first()->product->brand;

            return [
                'id'   => $brand->id,
                'name' => $brand->name,
                // Agrupamos por la categoría a la que pertenece el producto dentro de esta marca
                'subcategories' => $skusInBrand->groupBy('product.category_id')->map(function ($skusInCat) {
                    $category = $skusInCat->first()->product->category;
                    return [
                        'id'       => $category->id ?? 'uncategorized',
                        'name'     => $category->name ?? 'Otros',
                        'products' => $skusInCat->groupBy('product_id')->map(function ($skusForProduct) {
                            $product = $skusForProduct->first()->product;
                            $totalStock = $skusForProduct->sum('available_stock');
                            
                            return [
                                'id'              => $product->id,
                                'name'            => $product->name,
                                'image_url'       => $product->image_path ?? $skusForProduct->first()->image_url,
                                'available_stock' => (int) $totalStock,
                                'stock_status'    => $totalStock > 0 ? 'in_stock' : 'out_of_stock',
                                'min_price'       => $skusForProduct->min('current_unit_price'), // Usa el precio inyectado
                                
                                'skus' => $skusForProduct->map(fn($sku) => [
                                    'id'              => $sku->id,
                                    'name'            => $sku->name,
                                    'price'           => $sku->current_unit_price, // Usa el precio inyectado
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