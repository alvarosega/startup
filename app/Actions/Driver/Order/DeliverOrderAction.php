<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class DeliverOrderAction
{
    public function execute(string $orderId, string $driverId, string $providedOtp): void
    {
        DB::transaction(function () use ($orderId, $driverId, $providedOtp) {
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'arrived') {
                throw new Exception('Debe marcar su llegada antes de entregar el paquete.');
            }

            if ($order->driver_id !== $driverId) {
                throw new Exception('Violación de acceso logístico.');
            }

            // Validación estricta del PIN del Cliente
            if (!hash_equals((string) $order->delivery_otp, $providedOtp)) {
                throw new Exception('El PIN del cliente es incorrecto. Operación abortada.');
            }

            // Éxito: El paquete es del cliente, se destruye el OTP de entrega.
            $order->update([
                'status' => 'delivered',
                'delivery_otp' => null
            ]);
        });
    }
}