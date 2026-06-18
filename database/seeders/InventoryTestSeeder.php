<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Branch;
use App\Models\Sku;

class InventoryTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Extracción de dependencias estructurales
        $branchIds = DB::table('branches')->where('is_active', true)->pluck('id')->toArray();
        // LEY: El inventario físico solo se aplica a SKUs estándar, los bundles son virtuales
        $skuIds = DB::table('skus')->where('is_active', true)->where('is_bundle', false)->pluck('id')->toArray();
        $providerIds = DB::table('providers')->pluck('id')->toArray();
        $adminIds = DB::table('admins')->pluck('id')->toArray();

        if (empty($branchIds) || empty($skuIds) || empty($providerIds) || empty($adminIds)) {
            throw new \RuntimeException('Error: Se requiere que las tablas branches, skus, providers y admins tengan datos previos.');
        }

        $totalItemsToCreate = 1000;
        $itemsCreated = 0;
        $itemGlobalCounter = 0;

        DB::beginTransaction();
        try {
            // 2. Fase de Entrada de Mercadería (Purchases)
            while ($itemsCreated < $totalItemsToCreate) {
                $branchId = $branchIds[array_rand($branchIds)];
                $purchaseId = Str::uuid()->toString();

                // Apertura de cabecera de compra
                DB::table('purchases')->insert([
                    'id'              => $purchaseId,
                    'branch_id'       => $branchId,
                    'provider_id'     => $providerIds[array_rand($providerIds)],
                    'admin_id'        => $adminIds[array_rand($adminIds)],
                    'document_number' => 'FAC-' . strtoupper(Str::random(4)) . '-' . rand(10000, 99999),
                    'purchase_date'   => now()->subDays(rand(0, 30))->format('Y-m-d'),
                    'payment_type'    => rand(0, 1) ? 'CASH' : 'CREDIT',
                    'status'          => 'COMPLETED',
                    'deleted_epoch'   => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // Determinación del tamaño del lote de la factura (entre 5 y 15 artículos por documento)
                $itemsInThisPurchase = rand(5, 15);
                
                // Ajuste de cola para clavar el contador estrictamente en 1000
                if (($itemsCreated + $itemsInThisPurchase) > $totalItemsToCreate) {
                    $itemsInThisPurchase = $totalItemsToCreate - $itemsCreated;
                }

                for ($i = 0; $i < $itemsInThisPurchase; $i++) {
                    $skuId = $skuIds[array_rand($skuIds)];
                    $purchaseItemId = Str::uuid()->toString();
                    
                    // Volumen decimal simulado (unidades, kilos o litros)
                    $quantity = round((float) (rand(50, 500) + (rand(0, 9) / 10)), 3);

                    // Inserción de la línea del documento
                    DB::table('purchase_items')->insert([
                        'id'          => $purchaseItemId,
                        'purchase_id' => $purchaseId,
                        'sku_id'      => $skuId,
                        'quantity'    => $quantity,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    // 3. Fase de Atomización Dinámica (Lotes Físicos)
                    $itemGlobalCounter++;
                    $dice = rand(1, 100);
                    
                    // Distribución estadística: 10% Cuarentena, 10% Stock Seguridad, 80% Operativo Libre
                    $isQuarantine = ($dice <= 10);
                    $isSafety     = (!$isQuarantine && $dice > 90);

                    DB::table('inventory_lots')->insert([
                        'id'                => Str::uuid()->toString(),
                        'purchase_id'       => $purchaseId,
                        'transfer_id'       => null,
                        'branch_id'         => $branchId,
                        'sku_id'            => $skuId,
                        // Código único compuesto por sucursal e iterador global para evitar colisiones
                        'lot_code'          => 'LOTE-' . strtoupper(substr($branchId, 0, 3)) . '-' . str_pad((string)$itemGlobalCounter, 5, '0', STR_PAD_LEFT),
                        'quantity'          => $quantity,
                        'initial_quantity'  => $quantity,
                        'reserved_quantity' => 0.000, // Compras entran limpias de compromisos
                        'is_safety_stock'   => $isSafety,
                        'is_quarantine'     => $isQuarantine,
                        'expiration_date'   => now()->addDays(rand(30, 365))->format('Y-m-d'),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }

                $itemsCreated += $itemsInThisPurchase;
            }

            // 4. Fase de Consolidación Ascendente (Matriz de Balances)
            // Se calcula de forma masiva agrupando la realidad atómica actual de los lotes
            $aggregates = DB::table('inventory_lots')
                ->select([
                    'branch_id',
                    'sku_id',
                    DB::raw('SUM(quantity) as calc_physical'),
                    DB::raw('SUM(CASE WHEN is_safety_stock = 1 THEN quantity ELSE 0 END) as calc_safety'),
                    DB::raw('SUM(CASE WHEN is_quarantine = 1 THEN quantity ELSE 0 END) as calc_quarantine')
                ])
                ->groupBy('branch_id', 'sku_id')
                ->get();

            foreach ($aggregates as $row) {
                DB::table('inventory_balances')->updateOrInsert(
                    [
                        'branch_id' => $row->branch_id, 
                        'sku_id'    => $row->sku_id
                    ],
                    [
                        'total_physical'   => $row->calc_physical,
                        'total_reserved'   => 0.000, // Garantiza matemáticamente la restricción CHECK (Reserved = 0)
                        'total_safety'     => $row->calc_safety,
                        'total_quarantine' => $row->calc_quarantine,
                        'created_at'       => now(),
                        'updated_at'       => now()
                    ]
                );
            }

            DB::commit();
            $this->command->info("Simulación finalizada con éxito. Se procesaron {$itemsCreated} purchase_items y se consolidaron " . count($aggregates) . " balances de inventario.");

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}