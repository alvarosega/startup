<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Bundle, Branch, Provider};
use Illuminate\Database\Seeder;

class BundleBannerSeeder extends Seeder
{
    public function run(): void
    {
        $placement = AdPlacement::where('code', 'BUNDLE_HERO')->first();
        
        $campaign = AdCampaign::where('type', 'INTERNAL')->active()->first() ?? AdCampaign::create([
            'name' => 'Identidad de Packs 2026',
            'provider_id' => Provider::first()?->id,
            'type' => 'INTERNAL',
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'is_active' => true
        ]);

        if (!$placement) {
            $this->command->error('❌ Falta placement BUNDLE_HERO. Ejecuta primero AdPlacementSeeder.');
            return;
        }

        $branches = Branch::active()->get();

        foreach ($branches as $branch) {
            $bundles = Bundle::where('branch_id', $branch->id)->active()->get();

            foreach ($bundles as $bundle) {
                AdCreative::updateOrCreate(
                    [
                        'name'      => "HERO_FOR_{$bundle->slug}", 
                        'bundle_id' => $bundle->id, // ANCLAJE: Donde se muestra
                        'branch_id' => $branch->id
                    ],
                    [
                        'campaign_id'  => $campaign->id,
                        'placement_id' => $placement->id,
                        // CIRUGÍA: Usar la clase completa
                        'target_type'  => Bundle::class, 
                        'target_id'    => $bundle->id,
                        'image_mobile_path'  => "retail-media/bundles/hero_{$bundle->type}_mobile.webp",
                        'image_desktop_path' => "retail-media/bundles/hero_{$bundle->type}_desktop.webp",
                        'action_type'  => 'NAVIGATE', 
                        'sort_order'   => 1,
                        'is_active'    => true
                    ]
                );
            }
        }

        $this->command->info('✅ Banners internos de Packs desplegados.');
    }
}