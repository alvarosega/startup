<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;

class GetPreparationDataAction
{
    public function execute(string $orderId): array
    {
        $order = Order::with(['items', 'customer.profile', 'driver.profile']) // Inyectamos driver
            ->findOrFail($orderId);

        return [
            'order' => [
                'id'   => $order->id,
                'code' => $order->code,
                'status'   => $order->status,
                'driver'   => $order->driver ? [
                    'name' => $order->driver->profile->first_name . ' ' . $order->driver->profile->last_name,
                    'avatar' => $order->driver->profile->avatar_source,
                ] : null,
                'customer' => [
                    'name' => ($order->customer->profile->first_name ?? 'N/A') . ' ' . ($order->customer->profile->last_name ?? '')
                ],
                'items' => $order->items->map(fn($item) => [
                    'sku_id'   => $item->sku_id,
                    'name'     => $item->product_name . ' ' . $item->sku_name,
                    'quantity' => $item->quantity,
                    'image'    => $item->image_snapshot ? asset('storage/' . $item->image_snapshot) : null,
                ])
            ]
        ];
    }
}