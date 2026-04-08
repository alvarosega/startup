<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class VerifyPickupAction
{
    public function execute(string $orderId, string $driverId, string $providedOtp): void
    {
        DB::transaction(function () use ($orderId, $driverId, $providedOtp) {
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            // Bloqueo 1: Verificación de Estado (Punto de no retorno)
            if ($order->status !== 'ready_for_dispatch') {
                throw new Exception('Operación denegada. El almacén aún no ha finalizado el empaquetado.');
            }

            // Bloqueo 2: Verificación de Identidad
            if ($order->driver_id !== $driverId) {
                throw new Exception('Violación de acceso. Usted no es el conductor asignado a este pedido.');
            }

            // Bloqueo 3: Verificación Criptográfica para evitar Timing Attacks
            if (!hash_equals((string) $order->pickup_otp, $providedOtp)) {
                throw new Exception('El PIN de recogida es incorrecto. Revise con el administrador.');
            }

            // Éxito: Cambio de estado y destrucción de la llave
            $order->update([
                'status'     => 'dispatched',
                'pickup_otp' => null 
            ]);
        });
    }
}