<?php
declare(strict_types=1);
namespace App\Actions\Admin\Order;

use App\Models\Order;

class GetDispatchDataAction
{
    public function execute(string $orderId): array
    {
        $order = Order::with(['customer.profile'])->findOrFail($orderId);
        return [
            'order' => [
                'id'            => $order->id,
                'code'          => $order->code,
                'delivery_type' => $order->delivery_type,
                'pickup_otp'    => $order->pickup_otp,
                'customer'      => ['name' => $order->customer->profile->first_name . ' ' . $order->customer->profile->last_name],
            ]
        ];
    }
}