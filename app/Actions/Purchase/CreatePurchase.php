<?php

namespace App\Actions\Purchase;

use App\Models\Purchase;
use App\Models\InventoryLot;
use App\DTOs\Purchase\PurchaseData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePurchase
{
    public function execute(PurchaseData $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear Cabecera de Compra
            $purchase = Purchase::create([
                'branch_id' => $data->branch_id,
                'provider_id' => $data->provider_id,
                'user_id' => auth()->id(),
                'document_number' => $data->document_number,
                'purchase_date' => $data->purchase_date,
                'payment_type' => $data->payment_type,
                'payment_due_date' => $data->payment_due_date,
                'total_amount' => $data->total_amount,
                'notes' => $data->notes,
                'status' => 'COMPLETED', // Asumimos que ingresa directo al stock
            ]);

            // 2. Generar Lotes (Inventory Lots)
            foreach ($data->items as $item) {
                // Generar código de lote único: L-{YYMMDD}-{RANDOM}
                // Ejemplo: L-240125-X9Y2
                $lotCode = 'L-' . $data->purchase_date->format('ymd') . '-' . Str::upper(Str::random(4));

                InventoryLot::create([
                    'purchase_id' => $purchase->id,
                    'branch_id' => $data->branch_id,
                    'sku_id' => $item->sku_id,
                    'lot_code' => $lotCode,
                    'quantity' => $item->quantity,
                    'initial_quantity' => $item->quantity, // Para trazabilidad de consumo
                    'reserved_quantity' => 0,
                    'unit_cost' => $item->unit_cost,
                    'expiration_date' => $item->expiration_date,
                ]);
            }

            return $purchase;
        });
    }
}