<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Brand, Branch, Provider, Category};
use Illuminate\Database\Seeder;

class AdCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Garantizar proveedor y campaña maestra
        $provider = Provider::firstOrCreate(
            ['commercial_name' => 'Retail Media Internal'],
            [
                'company_name' => 'Retail Media Internal S.A.', 
                'slug'         => 'retail-media-internal', 
                'tax_id'       => '123456789',
                'is_active'    => true
            ]
        );

        $campaign = AdCampaign::updateOrCreate(
            ['name' => 'Campaña Maestra Retail 2026'],
            [
                'provider_id' => $provider->id,
                'type'        => 'INTERNAL',
                'starts_at'   => now()->subDay(),
                'ends_at'     => now()->addYear(),
                'is_active'   => true
            ]
        );

        // 2. Garantizar sucursal base de contingencia
        $branch = Branch::where('is_active', true)->first();
        if (!$branch) {
            $branch = Branch::firstOrCreate(
                ['name' => 'Sucursal Ficticia Matriz'],
                ['is_active' => true, 'is_default' => true]
            );
            $this->command->warn('⚠️ Sucursal base generada automáticamente.');
        }

        // 3. Garantizar destino polimórfico válido de contingencia (Category)
        $targetCategory = Category::first();
        if (!$targetCategory) {
            $targetCategory = Category::firstOrCreate(
                ['slug' => 'categoria-contingencia-ads'],
                ['name' => 'Categoría Contingencia Ads', 'is_active' => true]
            );
        }

        // 4. Garantizar espacio comercial y procesar marcas
        $homePlacement = AdPlacement::firstOrCreate(
            ['code' => 'BRAND_HERO'],
            ['name' => 'Brand Banner Hero', 'max_items' => 5, 'is_active' => true]
        );

        $brands = Brand::where('is_active', true)->get();
        
        if ($brands->isEmpty()) {
            for ($i = 1; $i <= 4; $i++) {
                $dummyBrand = Brand::firstOrCreate(
                    ['slug' => "marca-ficticia-$i"],
                    ['name' => "Marca Ficticia $i", 'is_active' => true]
                );
                $brands->push($dummyBrand);
            }
            $this->command->warn('⚠️ Marcas ficticias inyectadas.');
        }

        foreach ($brands as $brand) {
            AdCreative::updateOrCreate(
                [
                    'placement_id' => $homePlacement->id, 
                    'branch_id'    => $branch->id,
                    'brand_id'     => $brand->id, // RECTIFICACIÓN: Llave de exclusión mutua asignada
                ],
                [
                    'campaign_id'        => $campaign->id,
                    'sku_id'             => null,
                    'category_id'        => null,
                    'bundle_id'          => null,
                    'target_id'          => $targetCategory->id, // RECTIFICACIÓN: Almacena ID de destino permitido
                    'target_type'        => 'App\Models\Category', // RECTIFICACIÓN: String mapeado de destino autorizado
                    'name'               => "BRAND_HERO_{$brand->slug}",
                    'image_mobile_path'  => "retail-media/brands/{$brand->slug}_mobile.webp",
                    'image_desktop_path' => "retail-media/brands/{$brand->slug}_desktop.webp",
                    'action_type'        => 'NAVIGATE',
                    'is_active'          => true,
                    'sort_order'         => 0
                ]
            );
        }

        $this->command->info('✅ Silo de Retail Media sincronizado y blindado con datos ficticios.');
    }
}