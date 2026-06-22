<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Adjustment;

use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\DTOs\Admin\Inventory\Adjustment\IsolateToQuarantineData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class IsolateToQuarantine
{
    public function execute(IsolateToQuarantineData $data): void
    {
        DB::transaction(function () use ($data) {
            $lot = InventoryLot::where('id', $data->inventory_lot_id)
                ->lockForUpdate()
                ->firstOrFail();

            $available = $lot->quantity - $lot->reserved_quantity;

            if ($data->quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' => "CONCURRENCY_FAIL: Existencias insuficientes para aislamiento profiláctico."
                ]);
            }

            // Si se aísla el lote completo, mutamos su bandera directa. 
            // Si es parcial, se descuenta del ordinario y se procesa en el balance consolidado.
            if (abs($data->quantity - $lot->quantity) < 0.001) {
                $lot->is_quarantine = true;
            }
            
            $lot->quantity = DB::raw("quantity - {$data->quantity}");
            $lot->save();

            InventoryMovement::create([
                'branch_id'        => $lot->branch_id,
                'sku_id'           => $lot->sku_id,
                'inventory_lot_id' => $lot->id,
                'admin_id'         => Auth::id() ?? $lot->branch_id,
                'type'             => 'QUARANTINE_ISOLATION',
                'quantity'         => $data->quantity,
                'reference'        => 'INTERNAL_ADJUST_QUARANTINE',
                'reason'           => $data->reason
            ]);

            DB::table('inventory_balances')
                ->where('branch_id', $lot->branch_id)
                ->where('sku_id', $lot->sku_id)
                ->update([
                    'total_quarantine' => DB::raw("total_quarantine + {$data->quantity}"),
                    'updated_at'       => now()
                ]);
        });
    }
}