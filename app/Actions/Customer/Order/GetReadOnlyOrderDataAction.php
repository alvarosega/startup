<?php
declare(strict_types=1);
namespace App\Actions\Admin\Order\Data;

use App\Models\Order;

class GetReadOnlyOrderDataAction
{
    public function execute(string $orderId): array
    {
        $order = Order::with(['items', 'customer.profile', 'branch', 'driver.profile'])->findOrFail($orderId);
        return [
            'order' => [
                'id'            => $order->id,
                'code'          => $order->code,
                'status'        => $order->status,
                'delivery_type' => $order->delivery_type,
                'total_amount'  => (float) $order->total_amount,
                'created_at'    => $order->created_at->toDateTimeString(),
                'customer'      => ['name' => $order->customer->profile->first_name . ' ' . $order->customer->profile->last_name],
                'items'         => $order->items->map(fn($item) => [
                    'name'     => $item->product_name . ' ' . $item->sku_name,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ])
            ]
        ];
    }
}