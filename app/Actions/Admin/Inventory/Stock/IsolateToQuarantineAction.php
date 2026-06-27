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
     * Contiene y bloquea stock ordinario desviándolo hacia el estado preventivo de cuarentena.
     */
    public function execute(IsolateQuarantineDataDTO $dto, string $adminId): void
    {
        DB::transaction(function () use ($dto, $adminId) {
            $lot = InventoryLot::where('id', $dto->inventoryLotId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $lot->quantity < $dto->quantity) {
                throw new \InvalidArgumentException('Conflicto operativo: Stock ordinario remanente insuficiente en lote.');
            }

            $lot->quantity = DB::raw("quantity - {$dto->quantity}");
            
            // Si todo el saldo disponible se agota, marcamos el flag de cuarentena del lote completo
            if (abs((float)$lot->quantity - $dto->quantity) < 0.0001 || (float)$lot->quantity <= $dto->quantity) {
                $lot->is_quarantine = true;
            }
            
            $lot->saveQuietly();

            $refLot = InventoryLot::find($lot->id);

            InventoryMovement::insert([
                'id'               => (string) Str::uuid(),
                'branch_id'        => $dto->branchId,
                'sku_id'           => $dto->skuId,
                'inventory_lot_id' => $lot->id,
                'admin_id'         => $adminId,
                'type'             => 'BLOCK_QUARANTINE',
                'quantity'         => $dto->quantity,
                'balance_after'    => (float) $refLot->quantity,
                'reference'        => "Retención Preventiva",
                'reason'           => $dto->reason,
                'created_at'       => now(),
            ]);

            InventoryBalance::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->update([
                    'total_quarantine' => DB::raw("total_quarantine + {$dto->quantity}")
                ]);
        });
    }
}