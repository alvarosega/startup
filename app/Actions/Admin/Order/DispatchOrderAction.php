<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use Exception;

class DispatchOrderAction
{
    public function execute(string $orderId, string $providedOtp): void
    {
        $order = Order::lockForUpdate()->findOrFail($orderId);
        
        if ($order->status !== 'ready_for_dispatch') {
             throw new Exception('La orden debe estar lista para despacho.');
        }

        // Validación OTP de Grado Militar (Protección contra Timing Attacks)
        if (!hash_equals($order->pickup_otp, $providedOtp)) {
            throw new Exception('El PIN de recogida proporcionado es incorrecto.');
        }

        if (!$order->driver_id && $order->delivery_type === 'delivery') {
             throw new Exception('No se puede despachar una entrega a domicilio sin un conductor asignado.');
        }
        
        // Al despachar, el pickup_otp ya no sirve, lo borramos por seguridad.
        $order->update([
            'status' => 'dispatched',
            'pickup_otp' => null
        ]);
    }
}