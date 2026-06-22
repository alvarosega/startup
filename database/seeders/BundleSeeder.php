<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bundle\Bundle;
use App\Models\Bundle\BundleItem;
use App\Models\Catalog\Product;
use App\Models\Catalog\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BundleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // RECTIFICACIÓN: Resolver o crear producto padre para contingencias de SKUs huérfanos
            $product = Product::first() ?? Product::create([
                'id' => (string) Str::uuid(),
                'name' => 'LÍNEA DE BEBIDAS Y DESTILADOS CORE'
            ]);

            // Extraer componentes o forzar su creación controlada con 'product_id'
            $gin = Sku::where('code', 'GIN-GOR-750')->first() ?? Sku::create([
                'id' => (string) Str::uuid(), 
                'product_id' => $product->id, 
                'name' => 'GIN GORDONS 750ML', 
                'code' => 'GIN-GOR-750'
            ]);

            $tonica = Sku::where('code', 'TON-EVE-354')->first() ?? Sku::create([
                'id' => (string) Str::uuid(), 
                'product_id' => $product->id, 
                'name' => 'TONICA EVERVESS 354ML', 
                'code' => 'TON-EVE-354'
            ]);

            // Macro Agrupador 1: Combo Activo Temporal
            $bundlePromo = Bundle::create([
                'id' => (string) Str::uuid(),
                'name' => 'COMBO INTENSO VERANO (1 GIN + 4 TONICAS)',
                'image_path' => 'bundles/combo_verano.webp',
                'type' => 'OFFER',
                'starts_at' => now()->toDateTimeString(),
                'ends_at' => now()->addMonths(2)->toDateTimeString(),
                'is_active' => true
            ]);

            BundleItem::create([
                'id' => (string) Str::uuid(),
                'bundle_id' => $bundlePromo->id,
                'sku_id' => $gin->id,
                'quantity' => 1.000
            ]);

            BundleItem::create([
                'id' => (string) Str::uuid(),
                'bundle_id' => $bundlePromo->id,
                'sku_id' => $tonica->id,
                'quantity' => 4.000
            ]);

            // Macro Agrupador 2: Plantilla Comercial Reutilizable
            $bundleTemplate = Bundle::create([
                'id' => (string) Str::uuid(),
                'name' => 'PLANTILLA DUOPACK CERVEZA PACEÑA',
                'image_path' => null,
                'type' => 'TEMPLATE',
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => true
            ]);

            $cerveza = Sku::where('code', 'CER-PAC-473')->first() ?? Sku::create([
                'id' => (string) Str::uuid(), 
                'product_id' => $product->id, 
                'name' => 'CERVEZA PACEÑA LATA 473ML', 
                'code' => 'CER-PAC-473'
            ]);

            BundleItem::create([
                'id' => (string) Str::uuid(),
                'bundle_id' => $bundleTemplate->id,
                'sku_id' => $cerveza->id,
                'quantity' => 2.000
            ]);
        });
    }
}