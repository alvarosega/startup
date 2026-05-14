<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Purchase, InventoryLot, InventoryBalance, InventoryMovement, Branch, Provider, Admin, Sku};
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/inventory_entry.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        // 1. Carga de Diccionarios en Memoria para Resolución Atómica
        $branches = Branch::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();
            
        $providers = Provider::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();
            
        $admins = Admin::pluck('id', 'phone')->toArray();
        $skus = Sku::whereNotNull('code')->pluck('id', 'code')->toArray();

        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV vacío o ilegible.");
            return;
        }
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        // 2. Extracción y Agrupación en Memoria
        $purchases = [];
        $rowNumber = 1;

        while (($data = fgetcsv($file, 0, ',')) !== false) {
            $rowNumber++;
            if (empty(array_filter($data))) continue;
            
            if (count($headers) !== count($data)) {
                $this->command->error("CRÍTICO: Error de estructura en fila {$rowNumber}. Proceso abortado.");
                return;
            }

            $row = array_combine($headers, $data);
            $cleanRow = array_map(function($value) {
                $str = trim((string)$value);
                return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            }, $row);

            $docNumber = $cleanRow['document_number'] ?? null;
            if (!$docNumber) {
                $this->command->error("CRÍTICO: Fila {$rowNumber} sin 'document_number'.");
                return;
            }

            if (!isset($purchases[$docNumber])) {
                $purchases[$docNumber] = [];
            }
            $purchases[$docNumber][] = $cleanRow;
        }
        fclose($file);

        // 3. Procesamiento Transaccional del Motor
        DB::beginTransaction();

        try {
            foreach ($purchases as $docNumber => $group) {
                $headerData = $group[0];

                // Resolución de Llaves Foráneas de Cabecera
                $branchSlug = strtolower($headerData['branch_slug'] ?? '');
                $providerSlug = strtolower($headerData['provider_slug'] ?? '');
                $adminPhone = $headerData['admin_phone'] ?? '';

                $branchId = $branches[$branchSlug] ?? null;
                $providerId = $providers[$providerSlug] ?? null;
                $adminId = $admins[$adminPhone] ?? null;

                if (!$branchId || !$providerId || !$adminId) {
                    throw new Exception("Dependencia rota en Doc {$docNumber}: branch_slug, provider_slug o admin_phone no resueltos.");
                }

                // Cálculo Dinámico del Total Financiero
                $totalAmount = 0;
                foreach ($group as $item) {
                    $totalAmount += ((int)($item['quantity'] ?? 0) * (float)($item['unit_cost'] ?? 0));
                }

                // A. Persistencia de Cabecera (Purchase)
                $purchase = Purchase::firstOrCreate(
                    ['document_number' => $docNumber],
                    [
                        'branch_id'     => $branchId,
                        'provider_id'   => $providerId,
                        'admin_id'      => $adminId,
                        'purchase_date' => $headerData['purchase_date'],
                        'payment_type'  => strtoupper($headerData['payment_type'] ?? 'CASH'),
                        'total_amount'  => $totalAmount,
                        'status'        => 'COMPLETED',
                    ]
                );

                // B. Procesamiento del Detalle (Lotes, Kardex y Balances)
                foreach ($group as $index => $item) {
                    $realRowNumber = "Doc: {$docNumber} | Item: " . ($index + 1);

                    // Resolución Estricta de SKU
                    $skuCode = $item['sku_code'] ?? null;
                    if (!$skuCode || !isset($skus[$skuCode])) {
                        throw new Exception("{$realRowNumber} -> SKU Code '{$skuCode}' no existe en base de datos.");
                    }
                    $skuId = $skus[$skuCode];

                    // Validación Estricta de Trazabilidad Física
                    $lotCode = $item['lot_code'] ?? null;
                    if (!$lotCode) {
                        throw new Exception("{$realRowNumber} -> Ausencia crítica de 'lot_code'. Operación rechazada.");
                    }

                    $quantity = (int)($item['quantity'] ?? 0);
                    $unitCost = (float)($item['unit_cost'] ?? 0);
                    $isSafety = (bool)($item['is_safety_stock'] ?? false);

                    // 1. Creación del Lote Físico
                    $lot = InventoryLot::firstOrCreate(
                        ['lot_code' => $lotCode],
                        [
                            'purchase_id'       => $purchase->id,
                            'branch_id'         => $branchId,
                            'sku_id'            => $skuId,
                            'quantity'          => $quantity,
                            'initial_quantity'  => $quantity,
                            'reserved_quantity' => 0,
                            'is_safety_stock'   => $isSafety,
                            'unit_cost'         => $unitCost,
                            'expiration_date'   => $item['expiration_date'] ?? null,
                        ]
                    );

                    // Si el lote acaba de ser inyectado, registramos movimientos y saldos
                    if ($lot->wasRecentlyCreated) {
                        
                        // 2. Movimiento Inmutable (Kardex)
                        InventoryMovement::create([
                            'branch_id'        => $branchId,
                            'sku_id'           => $skuId,
                            'inventory_lot_id' => $lot->id,
                            'admin_id'         => $adminId,
                            'type'             => 'ENTRY_INITIAL', // Tipificación explícita de inventario inicial
                            'quantity'         => $quantity,
                            'unit_cost'        => $unitCost,
                            'reference'        => "APERTURA DOC: {$docNumber}",
                        ]);

                        // 3. Sumatoria de Balances (Lectura Rápida)
                        $balance = InventoryBalance::firstOrCreate(
                            ['branch_id' => $branchId, 'sku_id' => $skuId],
                            ['total_physical' => 0, 'total_reserved' => 0, 'total_safety' => 0]
                        );

                        $balance->increment('total_physical', $quantity);
                        if ($isSafety) {
                            $balance->increment('total_safety', $quantity);
                        }
                    }
                }
            }

            DB::commit();
            $this->command->info("ÉXITO: Motor de inventario procesado. Compras, lotes, balances y kardex generados.");

        } catch (Throwable $e) {
            DB::rollBack();
            $this->command->error("ABORTO TRANSACCIONAL: " . $e->getMessage());
            throw $e;
        }
    }
}