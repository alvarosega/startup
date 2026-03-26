<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Sku, Bundle, Branch, Provider};
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Garantizar Placement
        $placement = AdPlacement::firstOrCreate(
            ['code' => 'HOME_HERO'],
            ['name' => 'Carrusel Principal Home', 'max_items' => 5, 'is_active' => true]
        );

        // 2. Garantizar Campaña
        $campaign = AdCampaign::where('is_active', true)->first() ?? AdCampaign::create([
            'name' => 'Campaña de Lanzamiento 2026',
            'provider_id' => Provider::first()?->id ?? Provider::create(['commercial_name' => 'Default Provider'])->id,
            'type' => 'INTERNAL',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addYear(),
            'is_active' => true
        ]);

        // 3. Garantizar Sucursal
        $branch = Branch::where('is_active', true)->first();
        if (!$branch) {
            $this->command->error('❌ No existen sucursales. Corre el BranchSeeder primero.');
            return;
        }

        // --- VARIANTE 1: SKU DIRECTO ---
        if ($sku = Sku::active()->first()) {
            AdCreative::updateOrCreate(
                ['name' => 'HERO_SKU_' . $sku->name, 'branch_id' => $branch->id],
                [
                    'campaign_id' => $campaign->id, 'placement_id' => $placement->id,
                    'target_type' => 'sku', 'target_id' => $sku->id,
                    'image_mobile_path' => 'retail-media/hero/mobile_sku.webp',
                    'image_desktop_path' => 'retail-media/hero/desktop_sku.webp',
                    'action_type' => 'ADD_TO_CART', 'sort_order' => 1, 'is_active' => true
                ]
            );
        }

        // --- VARIANTE 2: SHOPPING TEMPLATE (Antiguo Editable) ---
        // RECTIFICACIÓN: Filtrar por type = 'template'
        if ($eb = Bundle::active()->where('type', 'template')->first()) {
            AdCreative::updateOrCreate(
                ['name' => 'HERO_TEMPLATE_' . $eb->name, 'branch_id' => $branch->id],
                [
                    'campaign_id' => $campaign->id, 'placement_id' => $placement->id,
                    'target_type' => 'bundle', 'target_id' => $eb->id,
                    'image_mobile_path' => 'retail-media/hero/mobile_bundle_edit.webp',
                    'image_desktop_path' => 'retail-media/hero/desktop_bundle_edit.webp',
                    'action_type' => 'NAVIGATE', 'sort_order' => 2, 'is_active' => true
                ]
            );
        }

        // --- VARIANTE 3: BUNDLE ATÓMICO (Antiguo Fijo) ---
        // RECTIFICACIÓN: Filtrar por type = 'atomic'
        if ($fb = Bundle::active()->where('type', 'atomic')->first()) {
            AdCreative::updateOrCreate(
                ['name' => 'HERO_BUNDLE_ATOMIC_' . $fb->name, 'branch_id' => $branch->id],
                [
                    'campaign_id' => $campaign->id, 'placement_id' => $placement->id,
                    'target_type' => 'bundle', 'target_id' => $fb->id,
                    'image_mobile_path' => 'retail-media/hero/mobile_bundle_fixed.webp',
                    'image_desktop_path' => 'retail-media/hero/desktop_bundle_fixed.webp',
                    'action_type' => 'ADD_TO_CART', 'sort_order' => 3, 'is_active' => true
                ]
            );
        }

        $this->command->info('✅ Hero Banners sincronizados con éxito.');
    }
}