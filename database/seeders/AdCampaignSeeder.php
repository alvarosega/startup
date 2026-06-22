<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RetailMedia\AdPlacement;
use App\Models\RetailMedia\AdCampaign;
use App\Models\RetailMedia\AdCreative;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Operations\Branch;
use App\Models\Operations\Provider;
use Illuminate\Support\Str;

class AdCampaignSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Garantizar proveedor aportando toda la matriz de campos obligatorios (NOT NULL)
        $provider = Provider::firstOrCreate(
            ['company_name' => 'RETAIL MEDIA INTERNAL S.A.'],
            [
                'id'            => (string) Str::uuid(),
                'slug'          => 'retail-media-internal', // RECTIFICACIÓN: Campo obligatorio inyectado
                'tax_id'        => '000000000',             // RECTIFICACIÓN: Campo obligatorio inyectado
                'deleted_epoch' => 0
            ]
        );

        $campaign = AdCampaign::updateOrCreate(
            ['name' => 'CAMPAÑA MAESTRA RETAIL 2026'],
            [
                'id'          => (string) Str::uuid(),
                'provider_id' => $provider->id,
                'type'        => 'INTERNAL',
                'starts_at'   => now()->subDay(),
                'ends_at'     => now()->addYear(),
                'is_active'   => true
            ]
        );

        // 2. Garantizar sucursal base de contingencia
        $branch = Branch::where('deleted_epoch', 0)->first();
        if (!$branch) {
            $branch = Branch::create([
                'id'            => (string) Str::uuid(),
                'name'          => 'SUCURSAL MATRIZ DE CONTINGENCIA',
                'deleted_epoch' => 0
            ]);
            $this->command->warn('⚠️ Sucursal base generada automáticamente.');
        }

        // 3. Garantizar destino válido de contingencia (Category)
        $targetCategory = Category::first();
        if (!$targetCategory) {
            $targetCategory = Category::create([
                'id'   => (string) Str::uuid(),
                'name' => 'CATEGORÍA CONTINGENCIA ADS'
            ]);
        }

        // 4. Garantizar espacio comercial (Placement)
        $homePlacement = AdPlacement::firstOrCreate(
            ['code' => 'BRAND_HERO'],
            ['id' => (string) Str::uuid(), 'name' => 'BRAND BANNER HERO', 'max_items' => 5, 'is_active' => true]
        );

        $brands = Brand::all();
        
        if ($brands->isEmpty()) {
            $brands = collect();
            for ($i = 1; $i <= 4; $i++) {
                $dummyBrand = Brand::create([
                    'id'   => (string) Str::uuid(),
                    'name' => "MARCA FICTICIA $i"
                ]);
                $brands->push($dummyBrand);
            }
            $this->command->warn('⚠️ Marcas ficticias inyectadas.');
        }

        foreach ($brands as $brand) {
            AdCreative::updateOrCreate(
                [
                    'placement_id' => $homePlacement->id, 
                    'branch_id'    => $branch->id,
                    'brand_id'     => $brand->id,
                ],
                [
                    'id'                 => (string) Str::uuid(),
                    'campaign_id'        => $campaign->id,
                    'sku_id'             => null,
                    'bundle_id'          => null,
                    'category_id'        => $targetCategory->id, 
                    'name'               => "BRAND_HERO_BRAND_" . $brand->id,
                    'image_mobile_path'  => "retail-media/brands/mobile_fallback.webp",
                    'image_desktop_path' => "retail-media/brands/desktop_fallback.webp",
                    'action_type'        => 'NAVIGATE',
                    'is_active'          => true,
                    'sort_order'         => 0
                ]
            );
        }

        $this->command->info('✅ Silo de Retail Media sincronizado libre de excepciones de nulidad.');
    }
}