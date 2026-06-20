<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\Order\OrderCancellationService;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'orders:release-expired';
    protected $description = 'Detecta órdenes en estado pending cuyo tiempo expiró y libera el stock.';

    public function handle(OrderCancellationService $cancellationService): int
    {
        // Solo buscamos órdenes 'pending' (FSM actual)
        $expiredOrders = Order::where('status', 'pending')
            ->where('reservation_expires_at', '<', now())
            ->with('items')
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No hay órdenes expiradas.');
            return self::SUCCESS;
        }

        foreach ($expiredOrders as $order) {
            $this->info("Procesando expiración para orden: {$order->code}");
            $cancellationService->handleExpiration($order);
        }

        $this->info("Se liberaron {$expiredOrders->count()} órdenes.");
        return self::SUCCESS;
    }
}