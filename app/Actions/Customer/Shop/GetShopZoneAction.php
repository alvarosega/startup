<?php

namespace App\Actions\Customer\Shop; // <--- NAMESPACE CORREGIDO

use App\Models\MarketZone;
use App\Models\Category;
use App\Models\Product;
use App\Http\Resources\Shop\ShopProductResource;

class GetShopZoneAction
{
    public function execute(MarketZone $zone, string $branchId): \Illuminate\Support\Collection
    {
        $rootCategories = Category::where('market_zone_id', $zone->id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $rootCategories->map(function ($parent) use ($branchId) {
            
            $children = Category::where('parent_id', $parent->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $subcategoriesData = $children->map(function ($child) use ($branchId) {
                
                $products = Product::query()
                    ->where('category_id', $child->id)
                    ->where('is_active', true)
                    ->with([
                        'brand',
                        'skus' => function ($q) use ($branchId) {
                            $q->where('is_active', true)
                              ->whereHas('inventoryLots', function ($qLot) use ($branchId) {
                                  $qLot->where('branch_id', $branchId)
                                       ->where('current_stock', '>', 0);
                              });
                        }
                    ])
                    ->take(15)
                    ->get();

                if ($products->isEmpty()) return null;

                $resourceCollection = ShopProductResource::collection($products);
                $resourceCollection->collection->each(function ($r) use ($branchId) {
                    $r->setContextBranch($branchId);
                });

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'products' => $resourceCollection->resolve()
                ];
            })->filter()->values();

            if ($subcategoriesData->isEmpty()) return null;

            return [
                'id' => $parent->id,
                'name' => $parent->name,
                'subcategories' => $subcategoriesData
            ];

        })->filter()->values();
    }
}