<?php

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;

class GetCustomerOrderDetailAction
{
    public function execute(GetCustomerOrderDTO $dto): array
    {
        // Se añade la columna 'delivery_otp' a la consulta
        $order = Order::where('id', $dto->orderId)
            ->where('customer_id', $dto->customerId)
            ->with(['items.sku', 'branch:id,name'])
            ->firstOrFail();

        // Matemáticas aisladas (Zero-Timezone) con protección contra Null
        $secondsRemaining = $order->reservation_expires_at 
            ? max(0, $order->reservation_expires_at->timestamp - now()->timestamp)
            : 0;

        return [
            'order' => $order,
            // Exponer OTP de forma condicional para Zero-Trust
            'delivery_otp' => $order->status === 'arrived' ? $order->delivery_otp : null,
            'payment_context' => [
                'seconds_remaining' => $secondsRemaining,
                'is_expired' => $order->status === 'expired' || ($order->reservation_expires_at && $secondsRemaining <= 0),
                'bank_details' => 'Banco Ficticio CTA: 123456789 (QR a implementar)',
            ]
        ];
    }
}