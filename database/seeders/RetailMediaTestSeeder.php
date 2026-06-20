<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RetailMediaTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Extracción y validación de dependencias del ecosistema básico
        $providerId = DB::table('providers')->value('id');
        $branchId   = DB::table('branches')->value('id');
        $skuId      = DB::table('skus')->where('is_active', true)->value('id');
        $categoryId = DB::table('categories')->value('id');
        $bundleId   = DB::table('bundles')->where('is_active', true)->value('id');
        $brandId    = DB::table('brands')->value('id');

        if (!$providerId || !$branchId || !$skuId || !$categoryId || !$bundleId || !$brandId) {
            throw new \RuntimeException(
                'Error Crítico: El sistema requiere la existencia previa de al menos 1 Proveedor, ' .
                '1 Sucursal, 1 SKU activo, 1 Categoría, 1 Combo activo y 1 Marca para inyectar Retail Media.'
            );
        }

        DB::beginTransaction();
        try {
            // 2. Inyección de Espacios Comerciales (Placements)
            $placements = [
                ['id' => Str::uuid()->toString(), 'name' => 'Banner Principal Superior', 'code' => 'HOME_HERO', 'max_items' => 5],
                ['id' => Str::uuid()->toString(), 'name' => 'Patrocinados de Búsqueda', 'code' => 'SEARCH_TOP', 'max_items' => 3],
                ['id' => Str::uuid()->toString(), 'name' => 'Venta Cruzada en Carrito', 'code' => 'CART_CROSS_SELL', 'max_items' => 2],
                ['id' => Str::uuid()->toString(), 'name' => 'Promociones Post-Pago', 'code' => 'ORDER_SUCCESS', 'max_items' => 4],
            ];

            foreach ($placements as $placement) {
                DB::table('ad_placements')->updateOrInsert(
                    ['code' => $placement['code']],
                    [
                        'id'         => $placement['id'],
                        'name'       => $placement['name'],
                        'max_items'  => $placement['max_items'],
                        'is_active'  => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Re-recolectar IDs reales de placements persistidos
            $placementMap = DB::table('ad_placements')->pluck('id', 'code')->toArray();

            // 3. Inyección de Campañas
            $campaigns = [
                ['id' => Str::uuid()->toString(), 'name' => 'Campaña Colectiva Invierno 2026', 'type' => 'INTERNAL'],
                ['id' => Str::uuid()->toString(), 'name' => 'Patrocinio Bodegas Premium Latam', 'type' => 'PAID'],
            ];

            foreach ($campaigns as $campaign) {
                DB::table('ad_campaigns')->insert([
                    'id'          => $campaign['id'],
                    'provider_id' => $providerId,
                    'name'        => $campaign['name'],
                    'type'        => $campaign['type'],
                    'starts_at'   => now()->subDays(2),
                    'ends_at'     => now()->addDays(30),
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // 4. Inyección de Piezas Creativas (Creatives) bajo reglas de exclusión mutua
            $creativesPayload = [
                // Caso A: Anclaje Global (Placement HOME_HERO) -> Lleva a un SKU
                [
                    'campaign_id'  => $campaigns[0]['id'],
                    'placement_id' => $placementMap['HOME_HERO'],
                    'sku_id'       => null,
                    'category_id'  => null,
                    'bundle_id'    => null,
                    'brand_id'     => null, // Al quedar todos null, se evalúa como global en el scope del índice
                    'target_id'    => $skuId,
                    'target_type'  => 'App\Models\Sku',
                    'name'         => 'Hero Banner: Lanzamiento Licores del Mes',
                ],
                // Caso B: Anclaje en Categoría -> Redirige a un Combo
                [
                    'campaign_id'  => $campaigns[1]['id'],
                    'placement_id' => $placementMap['SEARCH_TOP'],
                    'sku_id'       => null,
                    'category_id'  => $categoryId, // Anclado estrictamente a la categoría
                    'bundle_id'    => null,
                    'brand_id'     => null,
                    'target_id'    => $bundleId,
                    'target_type'  => 'App\Models\Bundle',
                    'name'         => 'Banner Categoría: Oferta de Packs Seleccionados',
                ],
                // Caso C: Anclaje en Combo -> Redirige a un SKU (Cross-Sell)
                [
                    'campaign_id'  => $campaigns[0]['id'],
                    'placement_id' => $placementMap['CART_CROSS_SELL'],
                    'sku_id'       => null,
                    'category_id'  => null,
                    'bundle_id'    => $bundleId, // Anclado estrictamente a la vista del combo
                    'brand_id'     => null,
                    'target_id'    => $skuId,
                    'target_type'  => 'App\Models\Sku',
                    'name'         => 'Cross Sell: Llévate un complemento para tu combo',
                ],
                // Caso D: Anclaje en Marca -> Redirige a una Categoría
                [
                    'campaign_id'  => $campaigns[1]['id'],
                    'placement_id' => $placementMap['ORDER_SUCCESS'],
                    'sku_id'       => null,
                    'category_id'  => null,
                    'bundle_id'    => null,
                    'brand_id'     => $brandId, // Anclado estrictamente a los filtros de marca
                    'target_id'    => $categoryId,
                    'target_type'  => 'App\Models\Category',
                    'name'         => 'Fidelización Post-Venta: Sigue explorando la marca',
                ],
            ];

            foreach ($creativesPayload as $index => $creative) {
                DB::table('ad_creatives')->insert([
                    'id'                 => Str::uuid()->toString(),
                    'campaign_id'        => $creative['campaign_id'],
                    'placement_id'       => $creative['placement_id'],
                    'branch_id'          => $branchId,
                    'sku_id'             => $creative['sku_id'],
                    'category_id'        => $creative['category_id'],
                    'bundle_id'          => $creative['bundle_id'],
                    'brand_id'           => $creative['brand_id'],
                    'version'            => 1,
                    'target_id'          => $creative['target_id'],
                    'target_type'        => $creative['target_type'],
                    'name'               => $creative['name'],
                    'image_mobile_path'  => 'seeders/placeholder_mobile.webp',
                    'image_desktop_path' => 'seeders/placeholder_desktop.webp',
                    'action_type'        => 'NAVIGATE',
                    'sort_order'         => $index,
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::commit();
            $this->command->info('Seeder de Retail Media finalizado con éxito (Placements, Campaigns y Creatives indexados).');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}