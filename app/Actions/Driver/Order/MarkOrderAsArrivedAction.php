<?php

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Exception;

class MarkOrderAsArrivedAction
{
    public function execute(string $orderId, string $driverId): void
    {
        $affectedRows = Order::where('id', $orderId)
            ->where('driver_id', $driverId)
            ->where('status', 'dispatched')
            ->update(['status' => 'arrived']);

        if ($affectedRows === 0) {
            throw new Exception('No se pudo actualizar el estado. Verifica que la orden esté en tránsito.');
        }
    }
}