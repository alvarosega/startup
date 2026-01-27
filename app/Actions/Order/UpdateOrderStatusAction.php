<?php

namespace App\Actions\Order;

use App\DTOs\Order\UpdateOrderStatusDTO;
use App\Models\Order;
use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Exception;

class UpdateOrderStatusAction
{
    public function execute(UpdateOrderStatusDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            
            $order = $dto->order;
            $oldStatus = $order->status;
            $newStatus = $dto->newStatus;

            // 1. GESTIÓN DE INVENTARIO (Side Effects)

            // CASO A: APROBAR PAGO (Review -> Confirmed)
            if ($oldStatus === 'review' && $newStatus === 'confirmed') {
                $this->finalizeStockDeduction($order);
            }

            // CASO B: RECHAZAR/CANCELAR (Cualquier estado de reserva -> Cancelled)
            if (in_array($oldStatus, ['pending_proof', 'review']) && $newStatus === 'cancelled') {
                $this->releaseReservation($order);
            }

            // 2. ACTUALIZACIÓN DE ESTADO
            $order->update([
                'status' => $newStatus,
                'rejection_reason' => $newStatus === 'cancelled' ? $dto->rejectionReason : null,
                'reviewed_at' => $newStatus === 'confirmed' ? now() : $order->reviewed_at
            ]);

            return $order;
        });
    }

    /**
     * Convierte Reserva en Venta (Resta quantity y reserved_quantity)
     */
    private function finalizeStockDeduction(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $qtyToDeduct = $item->quantity;

            $lots = InventoryLot::where('branch_id', $order->branch_id)
                ->where('sku_id', $item->sku_id)
                ->where('reserved_quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($qtyToDeduct <= 0) break;

                $amount = min($lot->reserved_quantity, $qtyToDeduct);

                $lot->decrement('quantity', $amount);
                $lot->decrement('reserved_quantity', $amount);

                $qtyToDeduct -= $amount;
            }
        }
    }

    /**
     * Libera la reserva (Resta reserved_quantity, MANTIENE quantity físico)
     */
    private function releaseReservation(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $qtyToRelease = $item->quantity;

            $lots = InventoryLot::where('branch_id', $order->branch_id)
                ->where('sku_id', $item->sku_id)
                ->where('reserved_quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($qtyToRelease <= 0) break;

                $amount = min($lot->reserved_quantity, $qtyToRelease);
                
                $lot->decrement('reserved_quantity', $amount);
                
                $qtyToRelease -= $amount;
            }
        }
    }
}