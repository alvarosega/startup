<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'orders:release-expired';
    protected $description = 'Libera el stock de las órdenes que excedieron el tiempo de reserva sin comprobante.';

    public function handle()
    {
        $expiredOrders = Order::where('status', 'pending_proof')
            ->where('reservation_expires_at', '<', Carbon::now())
            ->with('items')
            ->get();

        if ($expiredOrders->isEmpty()) {
            return;
        }

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                
                $this->info("Liberando orden: {$order->code}");

                // Devolver stock reservado
                foreach ($order->items as $item) {
                    $qtyToRelease = $item->quantity;

                    // Buscamos lotes que tengan reservas para este SKU en esa sucursal
                    // Ordenamos por created_at para deshacer en el mismo orden (FIFO) o inverso, 
                    // realmente da igual mientras liberemos la cantidad correcta.
                    $lots = InventoryLot::where('branch_id', $order->branch_id)
                        ->where('sku_id', $item->sku_id)
                        ->where('reserved_quantity', '>', 0)
                        ->get();

                    foreach ($lots as $lot) {
                        if ($qtyToRelease <= 0) break;

                        // Cuánto podemos liberar de este lote
                        $amount = min($lot->reserved_quantity, $qtyToRelease);
                        
                        $lot->decrement('reserved_quantity', $amount);
                        $qtyToRelease -= $amount;
                    }
                }

                // Marcar orden como cancelada
                $order->update([
                    'status' => 'cancelled',
                    'rejection_reason' => 'Tiempo de reserva agotado (Sistema)'
                ]);
            });
        }

        $this->info("Se liberaron {$expiredOrders->count()} órdenes.");
    }
}