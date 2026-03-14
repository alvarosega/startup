<?php

namespace App\Actions\Driver\Order;

use App\Models\Driver;
use App\Models\Order;
use Exception;
use Illuminate\Support\Str;

class TakeOrderAction
{
    /**
     * El conductor acepta el pedido mientras está en preparación.
     */
    public function execute(string $orderId, Driver $driver): void
    {
        // Generamos un Pickup OTP corto y legible (ej: A4B2D)
        $pickupOtp = strtoupper(Str::random(5)); 

        $affectedRows = Order::where('id', $orderId)
            ->where('branch_id', $driver->branch_id)
            ->where('status', 'preparing')
            ->whereNull('driver_id')
            ->update([
                'driver_id' => $driver->id,
                'pickup_otp' => $pickupOtp, // Guardamos el código que el CAJERO dictará
            ]);

        if ($affectedRows === 0) {
            throw new Exception('Objetivo no disponible o ya asignado a otro conductor.');
        }
    }
}