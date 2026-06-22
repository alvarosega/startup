<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalog\Product;
use App\Models\Catalog\Sku;
use App\Models\Operations\Branch;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Resolver nodo logístico indispensable
            $branch = Branch::first() ?? Branch::create([
                'id' => (string) Str::uuid(),
                'name' => 'SUCURSAL CENTRAL NORTE',
                'deleted_epoch' => 0
            ]);

            // 2. RECTIFICACIÓN: Resolver o crear producto padre para evitar la excepción de columna 'product_id'
            $product = Product::first() ?? Product::create([
                'id' => (string) Str::uuid(),
                'name' => 'LÍNEA DE BEBIDAS Y DESTILADOS CORE'
            ]);

            $skus = Sku::limit(3)->get();
            if ($skus->isEmpty()) {
                $skus = collect([
                    Sku::create([
                        'id' => (string) Str::uuid(), 
                        'product_id' => $product->id, // Inyección obligatoria
                        'name' => 'GIN GORDONS 750ML', 
                        'code' => 'GIN-GOR-750'
                    ]),
                    Sku::create([
                        'id' => (string) Str::uuid(), 
                        'product_id' => $product->id, // Inyección obligatoria
                        'name' => 'TONICA EVERVESS 354ML', 
                        'code' => 'TON-EVE-354'
                    ]),
                    Sku::create([
                        'id' => (string) Str::uuid(), 
                        'product_id' => $product->id, // Inyección obligatoria
                        'name' => 'CERVEZA PACEÑA LATA 473ML', 
                        'code' => 'CER-PAC-473'
                    ])
                ]);
            }

            foreach ($skus as $sku) {
                // 3. Inyección de Lotes Físicos
                InventoryLot::create([
                    'id' => (string) Str::uuid(),
                    'sku_id' => $sku->id,
                    'branch_id' => $branch->id,
                    'lot_code' => 'LT-' . strtoupper(Str::random(6)),
                    'quantity' => 150.000,
                    'initial_quantity' => 150.000,
                    'expiration_date' => now()->addMonths(12)->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                InventoryLot::create([
                    'id' => (string) Str::uuid(),
                    'sku_id' => $sku->id,
                    'branch_id' => $branch->id,
                    'lot_code' => 'LT-' . strtoupper(Str::random(6)),
                    'quantity' => 50.000,
                    'initial_quantity' => 50.000,
                    'expiration_date' => now()->addMonths(18)->toDateTimeString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Consolidación de Balance General
                InventoryBalance::updateOrCreate(
                    [
                        'sku_id'    => $sku->id,
                        'branch_id' => $branch->id
                    ],
                    [
                        'total_physical'   => 200.000,
                        'total_reserved'   => 0.000,
                        'total_safety'     => 20.000,
                        'total_quarantine' => 0.000,
                        'updated_at'       => now()
                    ]
                );
            }
        });
    }
}