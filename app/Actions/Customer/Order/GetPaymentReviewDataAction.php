<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;

class GetPaymentReviewDataAction
{
    public function execute(string $orderId): array
    {
        $order = Order::with(['customer.profile'])->findOrFail($orderId);

        return [
            'order' => [
                'id'           => $order->id,
                'code'         => $order->code,
                'total_amount' => (float) $order->total_amount,
                'proof_url'    => $order->proof_of_payment ? asset('storage/' . $order->proof_of_payment) : null,
                'customer'     => [
                    'name'  => ($order->customer->profile->first_name ?? 'N/A') . ' ' . ($order->customer->profile->last_name ?? ''),
                    'phone' => $order->customer->phone,
                ]
            ]
        ];
    }
}