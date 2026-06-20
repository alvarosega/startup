<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;

class GetOrderTrackingDataAction
{
    public function execute(string $orderId): array
    {
        $order = Order::with(['branch:id,name,address', 'customer.profile'])->findOrFail($orderId);

        return [
            'order' => [
                'id'            => $order->id,
                'code'          => $order->code,
                'status'        => $order->status,
                'total_amount'  => (float) $order->total_amount,
                'delivery_type' => $order->delivery_type,
                // Proveemos la longitud esperada del PIN para validación Frontend
                'otp_length'    => 5, 
                'branch'        => $order->branch,
                'customer'      => [
                    'name'  => ($order->customer->profile->first_name ?? 'N/A') . ' ' . ($order->customer->profile->last_name ?? ''),
                    'phone' => $order->customer->phone,
                ]
            ]
        ];
    }
}