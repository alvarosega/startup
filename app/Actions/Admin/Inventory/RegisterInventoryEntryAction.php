<?php

namespace App\Actions\Admin\Inventory;

use App\DTOs\Admin\Inventory\RegisterPurchaseDTO;
use App\Models\{Purchase, InventoryLot, InventoryMovement};
use Illuminate\Support\Facades\{DB, Log};
use Exception;

class RegisterInventoryEntryAction
{
    public function execute(RegisterPurchaseDTO $dto): Purchase
    {
        Log::info("Iniciando registro de compra: Doc #{$dto->document_number}");

        try {
            return DB::transaction(function () use ($dto) {
                // 1. Crear Cabecera
                $purchase = Purchase::create([
                    'branch_id'       => $dto->branch_id,
                    'provider_id'     => $dto->provider_id,
                    'admin_id'        => $dto->admin_id,
                    'document_number' => $dto->document_number,
                    'purchase_date'   => $dto->purchase_date,
                    'payment_type'    => $dto->payment_type,
                    'payment_due_date'=> $dto->payment_due_date,
                    'total_amount'    => $dto->total_amount,
                    'notes'           => $dto->notes,
                    'status'          => 'COMPLETED',
                ]);

                // 2. Crear Lotes y Movimientos
                foreach ($dto->items as $index => $item) {
                    $lot = InventoryLot::create([
                        'purchase_id'      => $purchase->id,
                        'branch_id'        => $dto->branch_id,
                        'sku_id'           => $item->sku_id,
                        'lot_code'         => $item->lot_code ?? 'LOT-' . strtoupper(now()->format('ymdHis')) . "-{$index}",
                        'quantity'         => $item->quantity,
                        'initial_quantity' => $item->quantity,
                        'unit_cost'        => $item->unit_cost,
                        'expiration_date'  => $item->expiration_date,
                    ]);

                    InventoryMovement::create([
                        'branch_id'        => $dto->branch_id,
                        'sku_id'           => $item->sku_id,
                        'inventory_lot_id' => $lot->id,
                        'admin_id'         => $dto->admin_id,
                        'type'             => 'ENTRY_PURCHASE',
                        'quantity'         => $item->quantity,
                        'unit_cost'        => $item->unit_cost,
                        'reference'        => "Compra #{$dto->document_number}",
                    ]);
                }

                Log::info("Compra registrada exitosamente: ID {$purchase->id}");
                return $purchase;
            });
        } catch (Exception $e) {
            Log::error("Fallo crítico en RegisterInventoryEntryAction: " . $e->getMessage(), [
                'document' => $dto->document_number,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-lanzar para que Laravel lo maneje o el front lo vea
        }
    }
}