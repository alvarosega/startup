<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use Exception;

class UnassignDriverAction
{
    public function execute(string $orderId): void
    {
        $order = Order::findOrFail($orderId);

        // Bloqueo de seguridad: No se puede desasignar si ya salió del almacén
        if ($order->status === 'dispatched') {
            throw new Exception("No se puede retirar al conductor; la carga ya está en tránsito.");
        }

        $order->update([
            'driver_id' => null,
            // El estado se mantiene (preparing o ready_for_dispatch)
        ]);
    }
}