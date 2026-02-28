<?php

namespace App\Actions\Admin\Inventory;

use App\Models\{Purchase, InventoryLot, InventoryMovement};
use App\DTOs\Admin\Inventory\RegisterPurchaseDTO;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Exception;

class RegisterInventoryEntryAction
{
    public function execute(RegisterPurchaseDTO $dto): Purchase
    {
        Log::info('--- INICIANDO TRANSACCIÓN DE INGRESO DE INVENTARIO ---', [
            'admin_id'  => $dto->admin_id,
            'branch_id' => $dto->branch_id,
            'is_emergency' => $dto->is_emergency
        ]);

        try {
            return DB::transaction(function () use ($dto) {
                // 1. Foliación Inmutable (Servidor manda)
                $prefix = $dto->is_emergency ? 'EMG' : 'CMP';
                $docNumber = "{$prefix}-" . now()->format('ymd') . "-" . strtoupper(Str::random(4));

                Log::info("Generando documento inmutable: {$docNumber}");

                $purchase = Purchase::create([
                    'branch_id'       => $dto->branch_id,
                    'provider_id'     => $dto->provider_id,
                    'admin_id'        => $dto->admin_id,
                    'document_number' => $docNumber,
                    'purchase_date'   => $dto->purchase_date,
                    'payment_type'    => $dto->payment_type,
                    'total_amount'    => $dto->total_amount,
                    'notes'           => $dto->notes,
                    'status'          => 'COMPLETED',
                ]);

                foreach ($dto->items as $item) {
                    // 2. Lote con Código RELOT/LOT automático
                    $lotPrefix = $dto->is_emergency ? 'RELOT' : 'LOT';
                    $lotCode = "{$lotPrefix}-" . now()->format('ymd') . "-" . strtoupper(Str::random(5));

                    $lot = InventoryLot::create([
                        'purchase_id'      => $purchase->id,
                        'branch_id'        => $dto->branch_id,
                        'sku_id'           => $item['sku_id'],
                        'lot_code'         => $lotCode,
                        'quantity'         => $item['quantity'],
                        'initial_quantity' => $item['quantity'],
                        'is_safety_stock'  => $dto->is_emergency,
                        'unit_cost'        => $item['unit_cost'],
                        'expiration_date'  => $item['expiration_date'] ?? null,
                    ]);

                    // 3. Kardex: Trazabilidad total
                    InventoryMovement::create([
                        'branch_id'        => $dto->branch_id,
                        'sku_id'           => $item['sku_id'],
                        'inventory_lot_id' => $lot->id,
                        'admin_id'         => $dto->admin_id,
                        'type'             => 'ENTRY_PURCHASE',
                        'quantity'         => $item['quantity'],
                        'unit_cost'        => $item['unit_cost'],
                        'reference'        => "Ingreso Automatizado #{$docNumber}",
                    ]);

                    Log::info("Lote {$lotCode} registrado y contabilizado en Kardex.");
                }

                Log::info("Transacción completada con éxito. Compra ID: {$purchase->id}");
                return $purchase;
            });

        } catch (Exception $e) {
            Log::error("Fallo crítico en RegisterInventoryEntryAction: " . $e->getMessage(), [
                'dto'  => (array) $dto,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Re-lanzar la excepción. El controlador o el manejador global de excepciones 
            // debe interceptarla para devolver una respuesta HTTP correcta (422 o 500).
            throw $e; 
        }
    }
}