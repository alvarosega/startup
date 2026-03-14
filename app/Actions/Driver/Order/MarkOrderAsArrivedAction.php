<?php

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Exception;
use Illuminate\Support\Str;

class MarkOrderAsArrivedAction
{
    public function execute(string $orderId, string $driverId): void
    {
        // Generamos un PIN de 4 dígitos numéricos para el cliente
        $deliveryOtp = (string) rand(1000, 9999);

        $affectedRows = Order::where('id', $orderId)
            ->where('driver_id', $driverId)
            ->where('status', 'dispatched')
            ->update([
                'status' => 'arrived',
                'delivery_otp' => $deliveryOtp // Se guarda en este momento exacto
            ]);

        if ($affectedRows === 0) {
            throw new Exception('No se puede marcar llegada. Verifica que el pedido esté en camino.');
        }

        // NOTA: Aquí es donde dispararías una notificación Push o SMS al cliente 
        // diciéndole: "Tu repartidor ha llegado. Tu código de entrega es: $deliveryOtp"
    }
}