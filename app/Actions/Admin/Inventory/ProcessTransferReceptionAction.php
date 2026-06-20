<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory;

use App\DTOs\Admin\Inventory\TransferIntakeDTO;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\InventoryBalance;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcessTransferReceptionAction
{
    public function execute(TransferIntakeDTO $dto): Transfer
    {
        return DB::transaction(function () use ($dto) {
            $transfer = Transfer::where('id', $dto->transferId)
                ->where('status', 'in_transit')
                ->lockForUpdate()
                ->first();

            if (!$transfer) {
                throw new RuntimeException("La transferencia no se encuentra en estado 'in_transit' o no existe.");
            }

            $dtoItemsMap = collect($dto->items)->keyBy('sku_id');
            $transferItems = TransferItem::where('transfer_id', $transfer->id)
                ->orderBy('sku_id')
                ->get();

            foreach ($transferItems as $item) {
                if (!$dtoItemsMap->has($item->sku_id)) {
                    throw new RuntimeException("Falta información de recepción para el SKU: {$item->sku_id}");
                }

                $qtyReceived = $dtoItemsMap->get($item->sku_id)['qty_received'];

                if ($qtyReceived > $item->qty_sent) {
                    throw new RuntimeException("La cantidad recibida no puede ser mayor que la enviada para el SKU: {$item->sku_id}");
                }

                $lostQuantity = $item->qty_sent - $qtyReceived;

                $originLotMovement = InventoryMovement::where('branch_id', $transfer->origin_branch_id)
                    ->where('sku_id', $item->sku_id)
                    ->where('type', 'OUT_TRANSFER')
                    ->where('reference', 'TRF_' . $transfer->code)
                    ->first();

                if (!$originLotMovement) {
                    throw new RuntimeException("No se encontró el movimiento de salida origen para rastrear el lote.");
                }

                $originLot = InventoryLot::find($originLotMovement->inventory_lot_id);

                $destBalance = InventoryBalance::where('branch_id', $transfer->destination_branch_id)
                    ->where('sku_id', $item->sku_id)
                    ->lockForUpdate()
                    ->first();

                if (!$destBalance) {
                    $destBalance = InventoryBalance::create([
                        'branch_id' => $transfer->destination_branch_id,
                        'sku_id' => $item->sku_id,
                        'total_physical' => 0,
                        'total_reserved' => 0,
                        'total_safety' => 0,
                        'total_quarantine' => 0
                    ]);
                }

                $destBalance->increment('total_physical', $qtyReceived);

                $destLot = InventoryLot::where('branch_id', $transfer->destination_branch_id)
                    ->where('lot_code', $originLot->lot_code)
                    ->first();

                if ($destLot) {
                    $destLot->increment('quantity', $qtyReceived);
                } else {
                    $destLot = InventoryLot::create([
                        'purchase_id' => null,
                        'transfer_id' => $transfer->id,
                        'branch_id' => $transfer->destination_branch_id,
                        'sku_id' => $item->sku_id,
                        'lot_code' => $originLot->lot_code,
                        'quantity' => $qtyReceived,
                        'initial_quantity' => $qtyReceived,
                        'reserved_quantity' => 0,
                        'is_safety_stock' => false,
                        'expiration_date' => $originLot->expiration_date
                    ]);
                }

                InventoryMovement::create([
                    'branch_id' => $transfer->destination_branch_id,
                    'sku_id' => $item->sku_id,
                    'inventory_lot_id' => $destLot->id,
                    'admin_id' => $dto->adminId,
                    'type' => 'IN_TRANSFER',
                    'quantity' => $qtyReceived,
                    'reference' => 'TRF_' . $transfer->code,
                    'reason' => null,
                    'created_at' => now()
                ]);

                if ($lostQuantity > 0) {
                    $originBalance = InventoryBalance::where('branch_id', $transfer->origin_branch_id)
                        ->where('sku_id', $item->sku_id)
                        ->lockForUpdate()
                        ->first();

                    $originBalance->decrement('total_physical', $lostQuantity);

                    InventoryMovement::create([
                        'branch_id' => $transfer->origin_branch_id,
                        'sku_id' => $item->sku_id,
                        'inventory_lot_id' => $originLot->id,
                        'admin_id' => $dto->adminId,
                        'type' => 'OUT_SCRAP',
                        'quantity' => $lostQuantity,
                        'reference' => 'TRF_DISCREPANCY_' . $transfer->code,
                        'reason' => 'PERDIDA_EN_TRANSITO',
                        'created_at' => now()
                    ]);
                }

                $item->update(['qty_received' => $qtyReceived]);
            }

            $transfer->update([
                'status' => 'completed',
                'received_by' => $dto->adminId,
                'received_at' => now()
            ]);

            return $transfer;
        });
    }
}