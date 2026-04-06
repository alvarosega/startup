<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order; // <--- ASEGÚRATE QUE SEA ESTE EL NAMESPACE

use App\Models\Order;

class GetPreparationDataAction
{
    /**
     * Carga la información necesaria para el Manifiesto de Picking.
     */
    public function execute(string $orderId): array
    {
        $order = Order::with(['items', 'customer.profile'])->findOrFail($orderId);

        return [
            'order' => [
                'id'   => $order->id,
                'code' => $order->code,
                'customer' => [
                    'name' => $order->customer->profile->first_name . ' ' . $order->customer->profile->last_name
                ],
                'items' => $order->items->map(fn($item) => [
                    'sku_id'   => $item->sku_id,
                    'name'     => $item->product_name . ' ' . $item->sku_name,
                    'quantity' => $item->quantity,
                    // Si tienes el storage link, usa asset('storage/...')
                    'image'    => $item->image_snapshot ? asset('storage/' . $item->image_snapshot) : null,
                ])
            ]
        ];
    }
}