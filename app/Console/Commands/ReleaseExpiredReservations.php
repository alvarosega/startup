<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\InventoryLot;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'orders:release-expired';
    protected $description = 'Libera stock reservado de órdenes no pagadas y restaura carritos.';

    public function handle()
    {
        // Buscar órdenes pendientes que ya vencieron
        $expiredOrders = Order::where('status', 'pending_proof')
            ->where('reservation_expires_at', '<', Carbon::now())
            ->with('items')
            ->get();

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                $this->info("Procesando orden vencida: {$order->code}");

                // 1. Liberar Stock Reservado
                foreach ($order->items as $item) {
                    $qtyToRelease = $item->quantity;

                    // Buscamos lotes que tengan reserva para este SKU en esta sucursal
                    // Decrementamos la reserva (FIFO inverso o general)
                    $lots = InventoryLot::where('branch_id', $order->branch_id)
                        ->where('sku_id', $item->sku_id)
                        ->where('reserved_quantity', '>', 0)
                        ->orderBy('created_at', 'asc') // Mismo orden que reserva
                        ->get();

                    foreach ($lots as $lot) {
                        if ($qtyToRelease <= 0) break;

                        $release = min($lot->reserved_quantity, $qtyToRelease);
                        $lot->decrement('reserved_quantity', $release);
                        $qtyToRelease -= $release;
                    }
                }

                // 2. Marcar Orden como Cancelada
                $order->update([
                    'status' => 'cancelled',
                    'rejection_reason' => 'Tiempo de reserva (5 min) agotado.'
                ]);

                // 3. Restaurar Carrito (Soft Delete)
                // Buscamos el último carrito borrado de este usuario en esta sucursal
                $cart = Cart::withTrashed()
                    ->where('user_id', $order->user_id)
                    ->where('branch_id', $order->branch_id)
                    ->latest('deleted_at') // El que se borró más recientemente
                    ->first();

                if ($cart) {
                    $cart->restore();
                    $this->info("Carrito {$cart->id} restaurado.");
                }
            });
        }
    }
}