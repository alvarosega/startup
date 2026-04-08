<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;

class GetReadyForDispatchDataAction
{
    /**
     * Recupera los datos de la orden lista para despacho, incluyendo el OTP de salida.
     */
    public function execute(string $orderId): array
    {
        $order = Order::with(['customer.profile', 'branch:id,name'])->findOrFail($orderId);

        return [
            'order' => [
                'id'            => $order->id,
                'code'          => $order->code,
                'status'        => $order->status,
                'delivery_type' => $order->delivery_type,
                'total_amount'  => (float) $order->total_amount,
                // El secreto que el Admin debe dictar:
                'pickup_otp'    => $order->pickup_otp, 
                'customer'      => [
                    'name' => ($order->customer->profile->first_name ?? 'N/A') . ' ' . ($order->customer->profile->last_name ?? '')
                ],
                'branch'        => $order->branch
            ]
        ];
    }
}