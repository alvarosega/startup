<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class MarkOrderAsReadyAction
{
    /**
     * Finaliza el empaquetado y genera los PINs logísticos.
     */
    public function execute(string $orderId): Order
    {
        return DB::transaction(function () use ($orderId) {
            // Bloqueo de fila para evitar condiciones de carrera (doble clic)
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'preparing') {
                throw new Exception("La orden debe estar en preparación para marcarse como lista.");
            }

            // GENERACIÓN DE ENTROPÍA LOGÍSTICA
            // pickup_otp: 5 dígitos. El Driver debe dictárselo al Admin para llevarse la caja.
            $pickupOtp = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // delivery_otp: 4 dígitos. El Cliente debe dictárselo al Driver para recibir la caja.
            $deliveryOtp = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $order->update([
                'status'       => 'ready_for_dispatch',
                'pickup_otp'   => $pickupOtp,
                'delivery_otp' => $deliveryOtp,
            ]);

            return $order;
        });
    }
}