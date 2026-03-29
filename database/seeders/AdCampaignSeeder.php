<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Brand, Branch, Provider, Category, Bundle, Sku};
use Illuminate\Database\Seeder;

class AdCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // 1. GARANTIZAR CAMPAÑA MAESTRA (INTERNAL)
        $campaign = AdCampaign::updateOrCreate(
            ['name' => 'Campaña Maestra Retail 2026'],
            [
                'provider_id' => Provider::first()?->id,
                'type'        => 'INTERNAL',
                'starts_at'   => now()->subDay(),
                'ends_at'     => now()->addYear(),
                'is_active'   => true
            ]
        );

        $branch = Branch::active()->first();
        if (!$branch) {
            $this->command->error('❌ Abortando: No hay sucursales activas.');
            return;
        }

        // 2. PROCESAR BANNERS DE CATEGORÍA (Pasillos)
        $catPlacement = AdPlacement::where('code', 'CATEGORY_HERO')->first();
        if ($catPlacement) {
            $categories = Category::active()->get();
            foreach ($categories as $category) {
                $sku = Sku::whereHas('product', fn($q) => $q->where('category_id', $category->id))
                    ->active()->first();
                
                if (!$sku) continue;

                AdCreative::updateOrCreate(
                    ['name' => "BANNER_CAT_{$category->slug}", 'branch_id' => $branch->id],
                    [
                        'campaign_id'  => $campaign->id,
                        'placement_id' => $catPlacement->id,
                        'category_id'  => $category->id,
                        'target_type'  => Sku::class,
                        'target_id'    => $sku->id,
                        'action_type'  => 'NAVIGATE',
                        'is_active'    => true
                    ]
                );
            }
        }

        // 3. PROCESAR BANNERS DE MARCA (Hero)
        $homePlacement = AdPlacement::where('code', 'BRAND_HERO')->first();
        if ($homePlacement) {
            $brands = Brand::active()->where('is_featured', true)->get();
            if ($brands->isEmpty()) $brands = Brand::active()->limit(3)->get();

            foreach ($brands as $brand) {
                AdCreative::updateOrCreate(
                    ['brand_id' => $brand->id, 'placement_id' => $homePlacement->id, 'branch_id' => $branch->id],
                    [
                        'campaign_id'  => $campaign->id,
                        'name'         => "BRAND_HERO_{$brand->slug}",
                        'target_type'  => Brand::class,
                        'target_id'    => $brand->id,
                        'image_mobile_path'  => "retail-media/brands/{$brand->slug}_mobile.webp",
                        'image_desktop_path' => "retail-media/brands/{$brand->slug}_desktop.webp",
                        'action_type'  => 'NAVIGATE',
                        'is_active'    => true
                    ]
                );
            }
        }

        // 4. PROCESAR BANNERS DE PACKS (Bundle View)
        $bundlePlacement = AdPlacement::where('code', 'BUNDLE_HERO')->first();
        if ($bundlePlacement) {
            $bundles = Bundle::active()->limit(5)->get();
            foreach ($bundles as $bundle) {
                AdCreative::updateOrCreate(
                    ['bundle_id' => $bundle->id, 'branch_id' => $branch->id],
                    [
                        'name'         => "HERO_PACK_{$bundle->slug}",
                        'campaign_id'  => $campaign->id,
                        'placement_id' => $bundlePlacement->id,
                        'target_type'  => Bundle::class,
                        'target_id'    => $bundle->id,
                        'action_type'  => 'NAVIGATE',
                        'is_active'    => true
                    ]
                );
            }
        }

        $this->command->info('✅ Silo de Retail Media sincronizado y blindado.');
    }
}