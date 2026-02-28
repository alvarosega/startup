<?php

namespace App\App\Console\Commands\Logistics;

use Illuminate\Console\Command;
use App\Models\{Order, InventoryLot};
use Illuminate\Support\Facades\DB;

class CancelExpiredOrdersCommand extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancela órdenes pendientes que superaron los 10 minutos y devuelve el stock';

    public function handle()
    {
        // 1. Buscar órdenes expiradas que sigan esperando comprobante
        $expiredOrders = Order::where('status', 'pending_proof')
            ->where('reservation_expires_at', '<', now())
            ->with('items')
            ->get();

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                // 2. Devolver Stock a los lotes (Lógica Inversa al Checkout)
                foreach ($order->items as $item) {
                    // Aquí la lógica depende de si guardaste de qué lote sacaste cada item.
                    // Si no lo hiciste, lo devolvemos al lote más nuevo o creamos un registro de ajuste.
                    // Lo más simple para tu estructura actual:
                    $lot = InventoryLot::where('sku_id', $item->sku_id)
                        ->where('branch_id', $order->branch_id)
                        ->orderBy('expiration_date', 'asc')
                        ->first();

                    if ($lot) {
                        $lot->decrement('reserved_quantity', $item->quantity);
                    }
                }

                // 3. Marcar como expirada
                $order->update(['status' => 'expired']);
                $this->info("Orden {$order->code} cancelada y stock devuelto.");
            });
        }
    }
}