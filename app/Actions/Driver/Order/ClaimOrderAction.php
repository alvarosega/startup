<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class ClaimOrderAction
{
    public function execute(string $orderId, string $driverId, string $otp): void
    {
        DB::transaction(function () use ($orderId, $driverId, $otp) {
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'ready_for_dispatch' || $order->driver_id !== null) {
                throw new Exception('Este pedido ya no está disponible para ser reclamado.');
            }

            // Verificación del OTP dictado por el Admin
            if (!hash_equals((string)$order->pickup_otp, $otp)) {
                throw new Exception('El PIN de recogida es incorrecto. Solicítelo al administrador.');
            }

            $order->update([
                'driver_id' => $driverId,
                'status'    => 'dispatched',
                'pickup_otp'=> null // Se quema el PIN tras el éxito
            ]);
        });
    }
}