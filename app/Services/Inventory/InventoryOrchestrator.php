<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class InventoryOrchestrator
{
    /**
     * Reserva stock de lotes específicos (FEFO) y actualiza el balance global.
     */
    public function reserve(string $skuId, string $branchId, int $quantity): void
    {
        DB::transaction(function () use ($skuId, $branchId, $quantity) {
            // 1. Bloqueo y obtención de lotes (FEFO)
            $lots = InventoryLot::where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->where('is_safety_stock', false)
                ->whereRaw('(quantity - reserved_quantity) > 0')
                ->orderBy('expiration_date', 'asc') 
                ->lockForUpdate()
                ->get(); // RECTIFICACIÓN: Flecha corregida.

            $availableTotal = $lots->sum(fn($l) => $l->quantity - $l->reserved_quantity);

            if ($availableTotal < $quantity) {
                throw new Exception("Stock insuficiente en lotes para SKU: {$skuId}");
            }

            // 2. Distribución en lotes
            $remaining = $quantity;
            foreach ($lots as $lot) {
                if ($remaining <= 0) break;
                $canTake = min($remaining, ($lot->quantity - $lot->reserved_quantity));
                $lot->increment('reserved_quantity', $canTake);
                $remaining -= $canTake;
            }
            $balance = DB::table('inventory_balances')
                ->where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->lockForUpdate() // <--- CRÍTICO: Bloquea el balance para evitar Lost Updates
                ->first();

            if (!$balance) {
                throw new Exception("Balance no encontrado para SKU: {$skuId}");
            }

            // 3. Sincronía con Balance Global
            DB::table('inventory_balances')
                ->where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->update([
                    'total_physical' => DB::raw("total_physical - {$quantity}"),
                    'total_reserved' => DB::raw("total_reserved + {$quantity}"),
                    'updated_at' => now()
                ]);
        });
    }

    /**
     * Libera una reserva (caso de expiración o rechazo) devolviendo el stock al balance.
     */
    public function release(string $skuId, string $branchId, int $quantity): void
    {
        DB::transaction(function () use ($skuId, $branchId, $quantity) {
            $lots = InventoryLot::where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->where('reserved_quantity', '>', 0)
                ->orderBy('created_at', 'desc') 
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;
            foreach ($lots as $lot) {
                if ($remaining <= 0) break;
                $canRelease = min($remaining, $lot->reserved_quantity);
                $lot->decrement('reserved_quantity', $canRelease);
                $remaining -= $canRelease;
            }

            // El stock vuelve de 'reservado' a 'físico disponible'
            DB::table('inventory_balances')
                ->where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->update([
                    'total_physical' => DB::raw("total_physical + {$quantity}"),
                    'total_reserved' => DB::raw("total_reserved - {$quantity}"),
                    'updated_at' => now()
                ]);
        });
    }

    /**
     * Confirma la venta: el stock reservado desaparece definitivamente.
     */
    public function commitReservation(string $skuId, string $branchId, int $quantity): void
    {
        DB::transaction(function () use ($skuId, $branchId, $quantity) {
            $lots = InventoryLot::where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->where('reserved_quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;
            foreach ($lots as $lot) {
                if ($remaining <= 0) break;
                $amount = min($lot->reserved_quantity, $remaining);
                
                // Reducción física del lote
                $lot->decrement('quantity', $amount);
                $lot->decrement('reserved_quantity', $amount);
                $remaining -= $amount;
            }

            // En el balance, solo bajamos el reservado (el físico ya se bajó en 'reserve')
            DB::table('inventory_balances')
                ->where('sku_id', $skuId)
                ->where('branch_id', $branchId)
                ->decrement('total_reserved', $quantity);
        });
    }
}