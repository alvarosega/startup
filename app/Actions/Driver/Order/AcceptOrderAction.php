<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class AcceptOrderAction
{
    public function execute(string $orderId, string $driverId): void
    {
        DB::transaction(function () use ($orderId, $driverId) {
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            // Bloqueo 1: Solo se puede tomar si está en preparación o listo
            if (!in_array($order->status, ['preparing', 'ready_for_dispatch'])) {
                throw new Exception('El pedido no está en un estado válido para asignación.');
            }

            // Bloqueo 2: Evita colisiones si dos drivers hacen clic al mismo milisegundo
            if ($order->driver_id !== null) {
                throw new Exception('Operación fallida. Este pedido acaba de ser asignado a otro conductor.');
            }

            // Actualización Táctica: No tocamos el 'status'
            $order->update([
                'driver_id' => $driverId
            ]);
        });
    }
}