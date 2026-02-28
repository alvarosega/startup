<?php

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;

class GetCustomerOrderDetailAction
{
    public function execute(GetCustomerOrderDTO $dto): array
    {
        $order = Order::where('id', $dto->orderId)
            ->where('customer_id', $dto->customerId)
            ->with(['items.sku', 'branch:id,name'])
            ->firstOrFail();

        // Matemáticas aisladas (Zero-Timezone)
        $secondsRemaining = max(0, $order->reservation_expires_at->timestamp - now()->timestamp);

        return [
            'order' => $order,
            'payment_context' => [
                'seconds_remaining' => $secondsRemaining,
                'is_expired' => $secondsRemaining <= 0,
                'bank_details' => 'Banco Ficticio CTA: 123456789 (QR a implementar)',
            ]
        ];
    }
}