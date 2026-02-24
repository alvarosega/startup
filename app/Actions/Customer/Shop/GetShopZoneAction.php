<?php

namespace App\Actions\Customer\Shop;

use App\Models\MarketZone;
use App\Models\Category;
use App\Http\Resources\Customer\Shop\ShopProductResource;

class GetShopZoneAction
{
    public function execute(MarketZone $zone, string $branchId): \Illuminate\Support\Collection
    {
        // 1. Cargamos el árbol usando 'children' que es el nombre en tu modelo
        $categories = Category::where('market_zone_id', $zone->id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($q) use ($branchId) { // <--- CAMBIO AQUÍ
                $q->where('is_active', true)
                  ->with(['products' => function ($pq) use ($branchId) {
                      $pq->where('is_active', true)
                         ->with(['brand', 'skus' => function ($sq) use ($branchId) {
                             $sq->where('is_active', true)
                                ->whereHas('inventoryLots', fn($l) => $l->where('branch_id', $branchId)->where('quantity', '>', 0))
                                ->with(['prices' => fn($pr) => $pr->where('branch_id', $branchId)]);
                         }]);
                  }]);
            }])
            ->orderBy('sort_order')
            ->get();

        return $categories->map(function ($parent) use ($branchId) {
            // 2. Aquí también cambiamos subcategories por children
            $subcategories = $parent->children->map(function ($child) use ($branchId) {
                if ($child->products->isEmpty()) return null;

                $resourceCollection = ShopProductResource::collection($child->products);
                $resourceCollection->each(fn($r) => $r->setContextBranch($branchId));

                return [
                    'id'       => $child->id,
                    'name'     => $child->name,
                    'products' => $resourceCollection->resolve()
                ];
            })->filter()->values();

            if ($subcategories->isEmpty()) return null;

            return [
                'id'            => $parent->id,
                'name'          => $parent->name,
                'subcategories' => $subcategories // Mantenemos el nombre de la llave para el JSON del frontend
            ];
        })->filter()->values();
    }
}