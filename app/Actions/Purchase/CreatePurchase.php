<?php

namespace App\Actions\Purchase;

use App\DTOs\Purchase\PurchaseData;
use App\Models\Purchase;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePurchase
{
    public function execute(PurchaseData $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            
            // Calcular total
            $totalAmount = 0;
            foreach ($data->items as $item) {
                $totalAmount += $item->quantity * $item->unitCost;
            }

            // 1. Crear Compra
            $purchase = Purchase::create([
                'branch_id' => $data->branchId,
                'provider_id' => $data->providerId,
                'user_id' => $data->userId,
                'document_number' => $data->documentNumber,
                'purchase_date' => $data->purchaseDate,
                'payment_type' => $data->paymentType,
                'payment_due_date' => $data->paymentType === 'CREDIT' ? $data->paymentDueDate : null,
                'total_amount' => $totalAmount,
                'notes' => $data->notes,
                'status' => 'COMPLETED'
            ]);

            // 2. Procesar Items
            foreach ($data->items as $item) {
                // Generar código de lote único (Ej: L-20231025-ABCD)
                $lotCode = 'L-' . date('Ymd') . '-' . strtoupper(Str::random(4));

                // a) Crear Lote (Stock)
                $lot = InventoryLot::create([
                    'purchase_id' => $purchase->id,
                    'branch_id' => $data->branchId,
                    'sku_id' => $item->skuId,
                    'lot_code' => $lotCode,
                    'quantity' => $item->quantity,
                    'initial_quantity' => $item->quantity,
                    'reserved_quantity' => 0,
                    'unit_cost' => $item->unitCost,
                    'expiration_date' => $item->expirationDate
                ]);

                // b) Registrar Movimiento (Kardex)
                InventoryMovement::create([
                    'branch_id' => $data->branchId,
                    'sku_id' => $item->skuId,
                    'inventory_lot_id' => $lot->id,
                    'user_id' => $data->userId,
                    'type' => 'purchase', // Entrada
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unitCost,
                    'reference' => 'Compra #' . $purchase->document_number
                ]);
            }

            return $purchase;
        });
    }
}