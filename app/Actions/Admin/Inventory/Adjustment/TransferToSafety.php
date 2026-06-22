<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Adjustment;

use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\DTOs\Admin\Inventory\Adjustment\TransferToSafetyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class TransferToSafety
{
    public function execute(TransferToSafetyData $data): void
    {
        DB::transaction(function () use ($data) {
            // 1. Bloqueo pesimista del lote seleccionado (Row-Level Locking)
            $lot = InventoryLot::where('id', $data->inventory_lot_id)
                ->lockForUpdate()
                ->firstOrFail();

            $availableLiquidity = $lot->quantity - $lot->reserved_quantity;

            if ($data->quantity > $availableLiquidity) {
                throw ValidationException::withMessages([
                    'quantity' => "CONCURRENCY_FAIL: El stock remanente cambió durante la lectura. Disponible líquido: {$availableLiquidity}."
                ]);
            }

            // 2. Modificación de balances internos del lote
            $lot->quantity        = DB::raw("quantity - {$data->quantity}");
            $lot->safety_quantity  = DB::raw("safety_quantity + {$data->quantity}");
            $lot->initial_safety_quantity = DB::raw("initial_safety_quantity + {$data->quantity}");
            $lot->save();

            // 3. Estampar auditoría en Kardex
            InventoryMovement::create([
                'branch_id'        => $lot->branch_id,
                'sku_id'           => $lot->sku_id,
                'inventory_lot_id' => $lot->id,
                'admin_id'         => Auth::id() ?? $lot->branch_id,
                'type'             => 'REGULAR_RESERVATION', // Tipo de movimiento mapeado
                'quantity'         => $data->quantity,
                'reference'        => 'INTERNAL_ADJUST_SAFETY',
                'reason'           => $data->reason
            ]);

            // 4. Actualizar Matriz de Saldos Consolidada (Opción B)
            // Nota: total_physical no varía porque el stock sigue en el almacén, solo cambia su naturaleza comercial
            DB::table('inventory_balances')
                ->where('branch_id', $lot->branch_id)
                ->where('sku_id', $lot->sku_id)
                ->update([
                    'total_safety' => DB::raw("total_safety + {$data->quantity}"),
                    'updated_at'   => now()
                ]);
        });
    }
}