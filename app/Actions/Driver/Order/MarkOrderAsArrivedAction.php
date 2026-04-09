<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Exception;

class MarkOrderAsArrivedAction
{
    public function execute(string $orderId, string $driverId): void
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'dispatched') {
            throw new Exception("El pedido no está en tránsito.");
        }

        if ($order->driver_id !== $driverId) {
            throw new Exception("Operación denegada. Custodia inválida.");
        }

        $order->update(['status' => 'arrived']);
    }
}