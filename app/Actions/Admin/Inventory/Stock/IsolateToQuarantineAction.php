<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Stock;

use App\DTOs\Admin\Inventory\IsolateQuarantineDataDTO;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IsolateToQuarantineAction
{
    /**
     * Contiene o libera stock de lote desviándolo hacia o desde el estado preventivo de cuarentena.
     */
    public function execute(IsolateQuarantineDataDTO $dto, string $adminId): void
    {
        DB::transaction(function () use ($dto, $adminId) {
            $lot = InventoryLot::where('id', $dto->inventoryLotId)
                ->lockForUpdate()
                ->firstOrFail();

            $qty = (float) $lot->quantity;

            if ($dto->isQuarantine) {
                if ($lot->is_quarantine) {
                    return;
                }

                $lot->is_quarantine = true;
                $lot->saveQuietly();

                InventoryMovement::insert([
                    'id'               => (string) Str::uuid(),
                    'branch_id'        => (string) $lot->branch_id,
                    'sku_id'           => (string) $lot->sku_id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id'         => $adminId,
                    'type'             => 'BLOCK_QUARANTINE',
                    'quantity'         => $qty,
                    'balance_after'    => $qty,
                    'reference'        => "Retención Preventiva",
                    'reason'           => "Aislamiento preventivo y bloqueo de lote en cuarentena.",
                    'created_at'       => now(),
                ]);

                InventoryBalance::where('branch_id', $lot->branch_id)
                    ->where('sku_id', $lot->sku_id)
                    ->update([
                        'total_quarantine' => DB::raw("total_quarantine + {$qty}")
                    ]);
            } else {
                if (!$lot->is_quarantine) {
                    return;
                }

                $lot->is_quarantine = false;
                $lot->saveQuietly();

                InventoryMovement::insert([
                    'id'               => (string) Str::uuid(),
                    'branch_id'        => (string) $lot->branch_id,
                    'sku_id'           => (string) $lot->sku_id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id'         => $adminId,
                    'type'             => 'RELEASE_QUARANTINE',
                    'quantity'         => $qty,
                    'balance_after'    => $qty,
                    'reference'        => "Liberación de Cuarentena",
                    'reason'           => "Retorno de lote a disponibilidad operativa regular.",
                    'created_at'       => now(),
                ]);

                InventoryBalance::where('branch_id', $lot->branch_id)
                    ->where('sku_id', $lot->sku_id)
                    ->update([
                        'total_quarantine' => DB::raw("GREATEST(0, total_quarantine - {$qty})")
                    ]);
            }
        });
    }
}