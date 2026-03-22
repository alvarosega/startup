<?php

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Sku, Category, Branch};
use Illuminate\Database\Seeder;

class CategoryBannerSeeder extends Seeder
{
    public function run(): void
    {
        $placement = AdPlacement::where('code', 'HOME_HERO')->first();
        $campaign = AdCampaign::where('is_active', true)->first();
        $branch = Branch::where('is_active', true)->first();
        $category = Category::where('is_active', true)->first();

        if (!$placement || !$campaign || !$branch || !$category) {
            $this->command->error('❌ Faltan dependencias para el CategoryBannerSeeder.');
            return;
        }

        // --- BANNER EXCLUSIVO DE CATEGORÍA ---
        $sku = Sku::whereHas('product', fn($q) => $q->where('category_id', $category->id))
                  ->active()
                  ->first();

        if ($sku) {
            AdCreative::updateOrCreate(
                [
                    'name' => 'BANNER_CAT_' . $category->name . '_' . $sku->name,
                    'category_id' => $category->id, // Anclado a la categoría
                    'branch_id' => $branch->id
                ],
                [
                    'campaign_id' => $campaign->id,
                    'placement_id' => $placement->id,
                    'target_type' => 'sku',
                    'target_id' => $sku->id,
                    'image_mobile_path' => "retail-media/categories/mobile_{$category->slug}.webp",
                    'image_desktop_path' => "retail-media/categories/desktop_{$category->slug}.webp",
                    'action_type' => 'ADD_TO_CART',
                    'sort_order' => 1,
                    'is_active' => true
                ]
            );
            $this->command->info("🎯 Banner exclusivo creado para la categoría: {$category->name}");
        }
    }
}