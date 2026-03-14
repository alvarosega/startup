<?php

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Exception;

class VerifyPickupAction
{
    /**
     * Valida el código de la tienda y activa el modo Despacho.
     */
    public function execute(string $orderId, string $driverId, string $inputOtp): void
    {
        $order = Order::where('id', $orderId)
            ->where('driver_id', $driverId)
            ->firstOrFail();

        // Validamos el código dictado por la sucursal
        if ($order->pickup_otp !== strtoupper($inputOtp)) {
            throw new Exception('Código de recogida incorrecto. Solicítalo al personal de la tienda.');
        }

        // Generamos el Delivery OTP final (el que el CLIENTE dará al conductor)
        $deliveryOtp = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $order->update([
            'status' => 'dispatched',
            'delivery_otp' => $deliveryOtp,
            'dispatched_at' => now(),
        ]);
    }
}