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
            $this->command->error('❌ Faltan dependencias básicas (Placement, Campaña, Branch o Categoría).');
            return;
        }

        // --- BÚSQUEDA DE SKU CON LOGGING ---
        // Buscamos un SKU cuyo producto pertenezca a la categoría activa
        $sku = Sku::whereHas('product', function ($q) use ($category) {
            $q->where('category_id', $category->id);
        })->where('is_active', true)->first();

        if (!$sku) {
            $this->command->warn("⚠️ No se encontró ningún SKU activo para la categoría: {$category->name}. El seeder se abortó.");
            return;
        }

        // --- PERSISTENCIA BLINDADA ---
        AdCreative::updateOrCreate(
            [
                'name'        => 'BANNER_CAT_' . $category->name . '_' . $sku->name,
                'category_id' => $category->id,
                'branch_id'   => $branch->id
            ],
            [
                'campaign_id'        => $campaign->id,
                'placement_id'       => $placement->id,
                // LEY DE POLIMORFISMO: Usamos la clase completa para evitar fallos de MorphMap
                'target_type'        => get_class($sku), 
                'target_id'          => $sku->id,
                'image_mobile_path'  => "retail-media/categories/mobile_{$category->slug}.webp",
                'image_desktop_path' => "retail-media/categories/desktop_{$category->slug}.webp",
                'action_type'        => 'ADD_TO_CART',
                'sort_order'         => 1,
                'is_active'          => true
            ]
        );

        $this->command->info("🎯 Banner exclusivo creado exitosamente.");
        $this->command->line("<info> - Categoría:</info> {$category->name}");
        $this->command->line("<info> - Producto:</info> {$sku->name}");
        $this->command->line("<info> - Sucursal:</info> {$branch->name}");
    }
}