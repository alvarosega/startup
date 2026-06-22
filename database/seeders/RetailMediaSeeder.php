<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// RECTIFICACIÓN: Namespaces calificados bajo el dominio RetailMedia
use App\Models\RetailMedia\AdPlacement;
use App\Models\RetailMedia\AdCampaign;
use App\Models\RetailMedia\AdCreative;
use App\Models\Operations\Branch;
use App\Models\Operations\Provider;
use App\Models\Bundle\Bundle;
use App\Models\Catalog\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RetailMediaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $placements = [
                ['name' => 'BANNER PRINCIPAL SUPERIOR', 'code' => 'HOME_HERO', 'max_items' => 5],
                ['name' => 'CINTILLO DE BÚSQUEDA INTERNA', 'code' => 'SEARCH_TOP', 'max_items' => 3],
                ['name' => 'CROSS SELLING PRE-CHECKOUT', 'code' => 'CART_CROSS_SELL', 'max_items' => 2]
            ];

            $placementModels = [];
            foreach ($placements as $pl) {
                $placementModels[$pl['code']] = AdPlacement::updateOrCreate(
                    ['code' => $pl['code']],
                    [
                        'id' => (string) Str::uuid(),
                        'name' => $pl['name'],
                        'max_items' => $pl['max_items'],
                        'is_active' => true
                    ]
                );
            }

            $branch = Branch::first() ?? Branch::create([
                'id' => (string) Str::uuid(), 'name' => 'SUCURSAL CENTRAL', 'deleted_epoch' => 0
            ]);
            
            $provider = Provider::where('deleted_epoch', 0)->first() ?? Provider::create([
                'id' => (string) Str::uuid(), 'company_name' => 'DIAGEO CORPORATE S.A.', 'deleted_epoch' => 0
            ]);

            $sku = Sku::first();
            $bundle = Bundle::first();

            $campaignPaid = AdCampaign::create([
                'id' => (string) Str::uuid(),
                'provider_id' => $provider->id,
                'name' => 'LANZAMIENTO EXCLUSIVO DIAGEO VERANO',
                'type' => 'PAID',
                'starts_at' => now()->toDateTimeString(),
                'ends_at' => now()->addMonths(1)->toDateTimeString(),
                'is_active' => true
            ]);

            $campaignInternal = AdCampaign::create([
                'id' => (string) Str::uuid(),
                'provider_id' => null,
                'name' => 'CAMPAÑA AUTOPROMOCIONAL COMBOS',
                'type' => 'INTERNAL',
                'starts_at' => now()->toDateTimeString(),
                'ends_at' => now()->addYear()->toDateTimeString(),
                'is_active' => true
            ]);

            if ($sku) {
                AdCreative::create([
                    'id' => (string) Str::uuid(),
                    'campaign_id' => $campaignPaid->id,
                    'placement_id' => $placementModels['HOME_HERO']->id,
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'name' => 'BANNER_HERO_PRODUCTO_INDIVIDUAL',
                    'image_mobile_path' => 'creatives/hero_sku_mobile.webp',
                    'image_desktop_path' => 'creatives/hero_sku_desktop.webp',
                    'action_type' => 'NAVIGATE',
                    'sort_order' => 1,
                    'is_active' => true
                ]);
            }

            if ($bundle) {
                AdCreative::create([
                    'id' => (string) Str::uuid(),
                    'campaign_id' => $campaignInternal->id,
                    'placement_id' => $placementModels['HOME_HERO']->id,
                    'branch_id' => $branch->id,
                    'bundle_id' => $bundle->id,
                    'name' => 'BANNER_AUTOCARRITO_COMBO_VERANO',
                    'image_mobile_path' => 'creatives/hero_bundle_mobile.webp',
                    'image_desktop_path' => 'creatives/hero_bundle_desktop.webp',
                    'action_type' => 'ADD_TO_CART',
                    'sort_order' => 2,
                    'is_active' => true
                ]);
            }
        });
    }
}