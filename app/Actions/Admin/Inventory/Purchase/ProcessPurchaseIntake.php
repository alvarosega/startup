<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseItem;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\DTOs\Admin\Inventory\Purchase\PurchaseData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

final class ProcessPurchaseIntake
{
    public function execute(PurchaseData $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            // 1. Instanciar cabecera de compra
            $purchase = Purchase::create([
                'branch_id'       => $data->branch_id,
                'provider_id'     => $data->provider_id,
                'admin_id'        => Auth::id() ?? $data->branch_id, // Fallback de seguridad
                'document_number' => $data->document_number,
                'purchase_date'   => $data->purchase_date,
                'payment_type'    => $data->payment_type,
                'status'          => 'COMPLETED',
            ]);

            foreach ($data->items as $item) {
                // 2. Registrar ítem del documento
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'sku_id'      => $item->sku_id,
                    'quantity'    => $item->quantity,
                ]);

                // 3. Inyectar Lote Físico (Estrategia Global por Documento)
                $lot = InventoryLot::create([
                    'purchase_id'             => $purchase->id,
                    'branch_id'               => $data->branch_id,
                    'sku_id'                  => $item->sku_id,
                    'lot_code'                => $data->lot_code,
                    'quantity'                => $item->quantity,
                    'initial_quantity'        => $item->quantity,
                    'safety_quantity'         => 0.000,
                    'initial_safety_quantity' => 0.000,
                    'reserved_quantity'       => 0.000,
                    'is_quarantine'           => false,
                    'expiration_date'         => $data->expiration_date,
                ]);

                // 4. Registrar movimiento en el Kardex Histórico
                InventoryMovement::create([
                    'branch_id'        => $data->branch_id,
                    'sku_id'           => $item->sku_id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id'         => Auth::id() ?? $data->branch_id,
                    'type'             => 'PURCHASE_INTAKE',
                    'quantity'         => $item->quantity,
                    'reference'        => "DOC_REF: {$data->document_number}",
                    'reason'           => 'Ingreso ordinario por abastecimiento de proveedor B2B.'
                ]);

                // 5. Mutación Atómica del Balance Consolidado (Opción B: Query Builder Puro)
                DB::table('inventory_balances')
                    ->updateOrInsert(
                        ['branch_id' => $data->branch_id, 'sku_id' => $item->sku_id],
                        [
                            'total_physical' => DB::raw("total_physical + {$item->quantity}"),
                            'updated_at'     => now(),
                        ]
                    );
            }

            return $purchase;
        });
    }
}