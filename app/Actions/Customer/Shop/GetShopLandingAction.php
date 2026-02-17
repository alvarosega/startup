<?php

namespace App\Actions\Customer\Shop; // <--- NAMESPACE CORREGIDO

use App\Models\MarketZone;
use App\Models\Category;
use App\Models\Bundle;
use App\Http\Resources\Shop\ShopCategoryResource;

class GetShopLandingAction
{
    public function execute(string $branchId): array
    {
        $zones = MarketZone::orderBy('name')->get();

        $zonesData = $zones->map(function($zone) {
            $rootCategories = Category::roots()
                ->where('market_zone_id', $zone->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($rootCategories->isEmpty()) return null;

            $aisles = ShopCategoryResource::collection($rootCategories)->resolve();

            return [
                'slug' => $zone->slug,
                'svg_id' => $zone->svg_id,
                'name' => $zone->name,
                'color' => $zone->hex_color,
                'aisles' => $aisles 
            ];
        })->filter()->keyBy('slug');

        $bundles = collect();
    
        if ($branchId) {
            $binaryId = (strlen($branchId) === 32) ? hex2bin($branchId) : $branchId;
            
            $bundles = Bundle::where('branch_id', $binaryId)
                ->where('is_active', true)
                ->latest()
                ->get()
                ->map(function($bundle) {
                    return [
                        'id' => bin2hex($bundle->getRawOriginal('id')), // Convertir a HEX para Vue
                        'name' => $bundle->name,
                        'image_url' => $bundle->image_path ? asset('storage/' . $bundle->image_path) : null,
                        'slug' => $bundle->slug,
                        'type' => 'bundle',
                        'price_display' => $bundle->fixed_price ? 'Bs '.$bundle->fixed_price : 'Ver Precio'
                    ];
                });
        }
    
        return [
            'zones' => $zonesData,
            'bundles' => $bundles
        ];
    }
}