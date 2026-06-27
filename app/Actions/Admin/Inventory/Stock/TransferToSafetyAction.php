<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Stock;

use App\DTOs\Admin\Inventory\TransferSafetyDataDTO;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferToSafetyAction
{
    /**
     * Traspasa stock ordinario disponible hacia el fondo reservado de seguridad.
     */
    public function execute(TransferSafetyDataDTO $dto, string $adminId): void
    {
        DB::transaction(function () use ($dto, $adminId) {
            $lot = InventoryLot::where('id', $dto->inventoryLotId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $lot->quantity < $dto->quantity) {
                throw new \InvalidArgumentException('Conflicto operativo: Stock ordinario remanente insuficiente en lote.');
            }

            $lot->quantity = DB::raw("quantity - {$dto->quantity}");
            $lot->safety_quantity = DB::raw("safety_quantity + {$dto->quantity}");
            $lot->saveQuietly();

            $refLot = InventoryLot::find($lot->id);

            InventoryMovement::insert([
                'id'               => (string) Str::uuid(),
                'branch_id'        => $dto->branchId,
                'sku_id'           => $dto->skuId,
                'inventory_lot_id' => $lot->id,
                'admin_id'         => $adminId,
                'type'             => 'BLOCK_SAFETY',
                'quantity'         => $dto->quantity,
                'balance_after'    => (float) $refLot->quantity,
                'reference'        => "Reserva de Seguridad Interna",
                'reason'           => "Asignación de colchón manual de contingencia logisítica.",
                'created_at'       => now(),
            ]);

            InventoryBalance::where('branch_id', $dto->branchId)
                ->where('sku_id', $dto->skuId)
                ->update([
                    'total_safety' => DB::raw("total_safety + {$dto->quantity}")
                ]);
        });
    }
}