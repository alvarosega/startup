<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sku, Branch, Provider, Purchase, InventoryLot, InventoryMovement, Admin};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/inventory.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Archivo CSV de inventario no encontrado.");
            return;
        }

        // 1. Mapeo de Relaciones (Corregido: company_name en lugar de name)
        $branches  = Branch::pluck('id', 'slug')->toArray();
        $skus      = Sku::pluck('id', 'code')->toArray();
        $providers = Provider::pluck('id', 'company_name')->toArray(); // <--- CAMBIO CLAVE
        $admin     = Admin::first();

        if (!$admin) {
            $this->command->error('No hay Admin. Ejecuta SuperAdminSeeder primero.');
            return;
        }

        $file = fopen($csvPath, 'r');
        fgetcsv($file, 0, ";"); // Omitir cabecera

        $count = 0;

        DB::transaction(function () use ($file, $branches, $skus, $providers, $admin, &$count) {
            while (($row = fgetcsv($file, 0, ";")) !== FALSE) {
                if (empty($row[0])) continue;

                // Resolución de IDs
                $skuId        = $skus[trim($row[0])] ?? null;
                $branchId     = $branches[trim($row[1])] ?? null;
                $providerName = trim($row[2]);
                $providerId   = $providers[$providerName] ?? null;

                if (!$skuId || !$branchId || !$providerId) {
                    $this->command->warn("Fila saltada: Referencia fallida. SKU: {$row[0]}, Branch: {$row[1]}, Provider: {$providerName}");
                    continue;
                }

                $qty      = intval($row[3]);
                $cost     = floatval($row[4]);
                $isSafety = (bool)$row[5];
                $date     = trim($row[6]);

                // 1. Cabecera de Compra
                $purchase = Purchase::create([
                    'branch_id'       => $branchId,
                    'provider_id'     => $providerId,
                    'admin_id'        => $admin->id,
                    'document_number' => ($isSafety ? 'EMG-' : 'INI-') . strtoupper(Str::random(6)),
                    'purchase_date'   => $date,
                    'total_amount'    => $qty * $cost,
                    'status'          => 'COMPLETED'
                ]);

                // 2. Lote
                $lot = InventoryLot::create([
                    'branch_id'        => $branchId,
                    'sku_id'           => $skuId,
                    'purchase_id'      => $purchase->id,
                    'lot_code'         => 'LOT-' . strtoupper(Str::random(6)),
                    'quantity'         => $qty,
                    'initial_quantity' => $qty,
                    'is_safety_stock'  => $isSafety,
                    'unit_cost'        => $cost,
                ]);

                // 3. Kardex
                InventoryMovement::create([
                    'branch_id'        => $branchId,
                    'sku_id'           => $skuId,
                    'inventory_lot_id' => $lot->id,
                    'admin_id'         => $admin->id,
                    'type'             => 'ENTRY_PURCHASE',
                    'quantity'         => $qty,
                    'unit_cost'        => $cost,
                    'reference'        => "Carga: {$purchase->document_number}"
                ]);

                // 4. Balances
                DB::table('inventory_balances')->updateOrInsert(
                    ['branch_id' => $branchId, 'sku_id' => $skuId],
                    [
                        'total_physical' => DB::raw("total_physical + {$qty}"),
                        'total_safety'   => $isSafety ? DB::raw("total_safety + {$qty}") : DB::raw("total_safety"),
                        'updated_at'     => now(),
                        'created_at'     => now()
                    ]
                );

                $count++;
            }
        });

        fclose($file);
        $this->command->info("✅ Inventario: $count registros procesados exitosamente.");
    }
}