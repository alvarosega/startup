<?php

namespace App\Actions\Customer\Shop;

use App\Models\MarketZone;
use App\Models\Bundle;
use App\Http\Resources\Customer\Shop\ShopCategoryResource;
use App\Http\Resources\Customer\Shop\ShopProductResource;

class GetShopLandingAction
{
    public function execute(string $branchId): array
    {
        // 1. Obtener Zonas con sus categorías raíz (Eager Loading para evitar N+1)
        $zonesData = MarketZone::with(['categories' => function ($q) {
            $q->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order');
        }])->get()->map(function ($zone) {
            if ($zone->categories->isEmpty()) return null;

            return [
                'slug'   => $zone->slug,
                'svg_id' => $zone->svg_id,
                'name'   => $zone->name,
                'color'  => $zone->hex_color,
                'aisles' => ShopCategoryResource::collection($zone->categories)->resolve()
            ];
        })->filter()->keyBy('slug');

        // 2. Obtener Bundles (Cero conversiones binarias, uso de String puro)
        $bundles = Bundle::where('branch_id', $branchId)
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn($bundle) => [
                'id'            => $bundle->id, // UUID String nativo
                'name'          => $bundle->name,
                'image_url'     => $bundle->image_path ? asset('storage/' . $bundle->image_path) : null,
                'slug'          => $bundle->slug,
                'type'          => 'bundle',
                'price_display' => $bundle->fixed_price ? 'Bs ' . number_format($bundle->fixed_price, 2) : 'Ver Precio'
            ]);

        return [
            'zones'   => $zonesData,
            'bundles' => $bundles
        ];
    }
}