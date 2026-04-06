<?php

namespace App\Actions\Driver\Order;

use App\Models\{Driver, Order};
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TakeOrderWithOtpAction
{
    public function execute(string $orderId, Driver $driver, string $otp): void
    {
        DB::transaction(function () use ($orderId, $driver, $otp) {
            $order = Order::where('id', $orderId)
                ->where('branch_id', $driver->branch_id)
                ->where('status', 'ready_for_dispatch')
                ->whereNull('driver_id')
                ->lockForUpdate() // Evita que dos drivers lo tomen al mismo tiempo
                ->first();

            if (!$order) {
                throw ValidationException::withMessages(['otp' => 'El pedido ya no está disponible o fue asignado.']);
            }

            // Verificación de OTP (Cajero -> Driver)
            if ($order->pickup_otp !== strtoupper($otp)) {
                throw ValidationException::withMessages(['otp' => 'Código de despacho incorrecto.']);
            }

            // Asignación y cambio de estado
            $order->update([
                'driver_id' => $driver->id,
                'status'    => 'dispatched', // Pasa a "En Camino" inmediatamente
            ]);
        });
    }
}