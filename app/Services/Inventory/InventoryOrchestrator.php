<?php

namespace App\Services\Inventory;

use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryOrchestrator
{
    /**
     * Reserva stock de lotes específicos usando la lógica FEFO.
     * Bloquea las filas para evitar colisiones de stock.
     */
    public function reserve(string $skuId, string $branchId, int $quantity): void
    {
        // 1. Recuperar lotes con stock disponible, ordenados por vencimiento (FEFO)
        $lots = InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('is_safety_stock', false) // Protegemos el stock de seguridad
            ->whereRaw('(quantity - reserved_quantity) > 0')
            ->orderBy('expiration_date', 'asc') 
            ->lockForUpdate() // BLOQUEO CRÍTICO: Nadie toca estos lotes hasta que termine la transacción
            ->get();

        $availableTotal = $lots->sum(fn($l) => $l->quantity - $l->reserved_quantity);

        if ($availableTotal < $quantity) {
            throw new Exception("Stock insuficiente para completar la operación solicitada.");
        }

        $remainingToReserve = $quantity;

        // 2. Distribuir la reserva entre los lotes disponibles
        foreach ($lots as $lot) {
            if ($remainingToReserve <= 0) break;

            $availableInLot = $lot->quantity - $lot->reserved_quantity;
            $canTakeFromThisLot = min($remainingToReserve, $availableInLot);

            $lot->increment('reserved_quantity', $canTakeFromThisLot);
            $remainingToReserve -= $canTakeFromThisLot;
        }
    }

    public function release(string $skuId, string $branchId, int $quantity): void
    {
        // 1. Buscamos lotes que tengan reservas activas para este SKU
        // Usamos LIFO (Last In, First Out) para liberar, o simplemente el orden de reserva
        $lots = InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('reserved_quantity', '>', 0)
            ->orderBy('created_at', 'desc') 
            ->lockForUpdate() // Bloqueo de fila para evitar colisiones
            ->get();

        $remainingToRelease = $quantity;

        foreach ($lots as $lot) {
            if ($remainingToRelease <= 0) break;

            // Calculamos cuánto podemos liberar de este lote específico
            $canReleaseFromThisLot = min($remainingToRelease, $lot->reserved_quantity);

            // DECREMENTAMOS la reserva, NO la cantidad total.
            // El stock vuelve a estar 'disponible' automáticamente al bajar la reserva.
            $lot->decrement('reserved_quantity', $canReleaseFromThisLot);
            
            $remainingToRelease -= $canReleaseFromThisLot;
        }

        if ($remainingToRelease > 0) {
            // Esto no debería pasar en un sistema íntegro, pero lo logueamos por seguridad
            Log::error("Inconsistencia: Se intentó liberar {$quantity} unidades de SKU {$skuId}, pero solo se encontraron reservas para " . ($quantity - $remainingToRelease));
        }
    }

    public function commitReservation(string $skuId, string $branchId, int $quantity): void
    {
        // Importante usar el modelo correcto o asegurarte de que InventoryLot esté importado arriba
        $lots = \App\Models\InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('reserved_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $qtyToDeduct = $quantity;

        foreach ($lots as $lot) {
            if ($qtyToDeduct <= 0) break;

            $amount = min($lot->reserved_quantity, $qtyToDeduct);

            // Al confirmar la venta, el stock físico ('quantity') desaparece, 
            // y también borramos la "reserva" ('reserved_quantity') porque ya no aplica.
            $lot->decrement('quantity', $amount);
            $lot->decrement('reserved_quantity', $amount);

            $qtyToDeduct -= $amount;
        }

        if ($qtyToDeduct > 0) {
            \Illuminate\Support\Facades\Log::error("Error crítico de inventario al confirmar venta. SKU: {$skuId}");
        }
    }
}