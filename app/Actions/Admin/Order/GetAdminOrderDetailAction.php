<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;

class GetAdminOrderDetailAction
{
    public function execute(string $orderId): array
    {
        $order = Order::with([
            'items.sku.product', 
            'customer.profile', 
            'branch:id,name'
        ])->findOrFail($orderId);

        return [
            'order' => [
                'id'             => $order->id,
                'code'           => $order->code,
                'status'         => $order->status,
                'delivery_type'  => $order->delivery_type,
                'total_amount'   => (float) $order->total_amount,
                'created_at'     => $order->created_at->toDateTimeString(),
                'proof_url' => $order->proof_of_payment ? route('admin.orders.show-proof', $order->code) : null,
                'pickup_otp'     => $order->pickup_otp,
                'customer' => [
                    'name'  => $order->customer->profile->first_name . ' ' . $order->customer->profile->last_name,
                    'phone' => $order->customer->phone,
                ],
                'branch' => $order->branch,
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