<?php

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Sku, Category, Branch};
use Illuminate\Database\Seeder;

class CategoryBannerSeeder extends Seeder
{
    public function run(): void
    {
        $placement = AdPlacement::where('code', 'CATEGORY_HERO')->first();
        $campaign = AdCampaign::where('is_active', true)->first();
        $branch = Branch::where('is_active', true)->first();
        $categories = Category::active()->get();

        if (!$placement || !$campaign || !$branch || $categories->isEmpty()) {
            $this->command->error('❌ Faltan dependencias (Placement CATEGORY_HERO, Campaña, Branch o Categorías).');
            return;
        }

        foreach ($categories as $category) {
            $sku = Sku::whereHas('product', fn($q) => $q->where('category_id', $category->id))
                ->active()
                ->first();

            if (!$sku) continue;

            AdCreative::updateOrCreate(
                [
                    'name'        => 'BANNER_CAT_' . $category->slug,
                    'category_id' => $category->id,
                    'branch_id'   => $branch->id
                ],
                [
                    'campaign_id'  => $campaign->id,
                    'placement_id' => $placement->id,
                    'target_type'  => Sku::class, 
                    'target_id'    => $sku->id,
                    
                    // CORRECCIÓN QUIRÚRGICA: Forzamos null para activar placeholders
                    'image_mobile_path'  => null,
                    'image_desktop_path' => null,
                    
                    'action_type'  => 'NAVIGATE',
                    'sort_order'   => 1,
                    'is_active'    => true
                ]
            );
        }

        $this->command->info('🎯 Banners de categoría vinculados (Sin imágenes, usando placeholders).');
    }
}