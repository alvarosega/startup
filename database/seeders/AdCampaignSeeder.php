<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{AdPlacement, AdCampaign, AdCreative, Brand, Branch, Provider, Category, Bundle, Sku};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // 1. GARANTIZAR PROVEEDOR Y CAMPAÑA MAESTRA
        // 1. GARANTIZAR PROVEEDOR Y CAMPAÑA MAESTRA
        $provider = Provider::firstOrCreate(
            ['commercial_name' => 'Retail Media Internal'],
            [
                'company_name' => 'Retail Media Internal S.A.', 
                'slug'         => 'retail-media-internal', 
                'tax_id'       => '123456789', // <--- CAMPO OBLIGATORIO AÑADIDO
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

        // 2. GARANTIZAR SUCURSAL BASE (Fallback estricto)
        $branch = Branch::active()->first();
        if (!$branch) {
            $branch = Branch::firstOrCreate(
                ['name' => 'Sucursal Ficticia Matriz'],
                ['is_active' => true, 'is_default' => true]
            );
            $this->command->warn('⚠️ Sucursal base generada automáticamente.');
        }

        // 3. PROCESAR BANNERS DE MARCA (Hero) CON DATOS FICTICIOS
        $homePlacement = AdPlacement::firstOrCreate(
            ['code' => 'BRAND_HERO'],
            ['name' => 'Brand banner', 'max_items' => 5, 'is_active' => true]
        );

        $brands = Brand::active()->where('is_featured', true)->get();
        
        // INYECCIÓN DE DATOS FICTICIOS SI ESTÁ VACÍO
        if ($brands->isEmpty()) {
            for ($i = 1; $i <= 4; $i++) {
                $dummyBrand = Brand::firstOrCreate(
                    ['slug' => "marca-ficticia-$i"],
                    ['name' => "Marca Ficticia $i", 'is_active' => true, 'is_featured' => true]
                );
                $brands->push($dummyBrand);
            }
            $this->command->warn('⚠️ Marcas ficticias inyectadas.');
        }

        foreach ($brands as $brand) {
            AdCreative::updateOrCreate(
                [
                    'placement_id' => $homePlacement->id, 
                    'branch_id' => $branch->id,
                    'target_id' => $brand->id,
                    'target_type' => Brand::class
                ],
                [
                    'campaign_id'  => $campaign->id,
                    'name'         => "BRAND_HERO_{$brand->slug}",
                    // Uso de variables dinámicas para evitar colisiones de esquema si 'brand_id' no existe en DB
                    'image_mobile_path'  => "retail-media/brands/{$brand->slug}_mobile.webp",
                    'image_desktop_path' => "retail-media/brands/{$brand->slug}_desktop.webp",
                    'action_type'  => 'NAVIGATE',
                    'is_active'    => true,
                    'sort_order'   => 0
                ]
            );
        }

        // 4. (Mantener la lógica para BUNDLES y CATEGORY con el mismo patrón firstOrCreate si lo requieres)
        // Por eficiencia para tu problema actual, este bloque garantiza los Brand Banners.

        $this->command->info('✅ Silo de Retail Media sincronizado y blindado con datos ficticios.');
    }
}