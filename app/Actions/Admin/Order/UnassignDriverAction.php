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

        // REGLA DE NEGOCIO: Prohibir desasignación si ya se dictó el PIN y el driver se fue.
        if (in_array($order->status, ['dispatched', 'arrived', 'delivered', 'completed'])) {
            throw new Exception("Operación abortada: La carga ya salió del almacén o ha sido entregada.");
        }

        $order->update([
            'driver_id' => null
        ]);
    }
}