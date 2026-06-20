<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use Exception;

class GetPaymentReviewDataAction
{
    public function execute(string $id): array
    {
        // NOTA: El controller nos pasa el UUID ($order->id) internamente, lo cual es correcto.
        $order = Order::with(['customer.profile', 'branch', 'items'])->findOrFail($id);

        if ($order->status !== 'payment_pending') {
            throw new Exception("El pago de esta orden no está pendiente de revisión.");
        }

        return [
            'order' => [
                'id'           => $order->id,
                'code'         => $order->code,
                'total_amount' => (float) $order->total_amount,
                'status'       => $order->status,
                'created_at'   => $order->created_at->toISOString(),
                // RECTIFICACIÓN CRÍTICA: Generar URL usando el CODE para que la ruta lo reconozca
                'proof_url'    => route('admin.orders.show-proof', $order->code), 
                'delivery_type'=> $order->delivery_type,
            ],
            'customer' => [
                'name'  => $order->customer->profile->first_name . ' ' . $order->customer->profile->last_name,
                'phone' => $order->customer->phone,
            ],
            'branch' => [
                'name' => $order->branch->name,
            ],
            'items' => $order->items->map(fn($item) => [
                'id'             => $item->id,
                'sku_name'       => $item->sku_name,
                'product_name'   => $item->product_name,
                'quantity'       => $item->quantity,
                'unit_price'     => (float) $item->unit_price,
                'subtotal'       => (float) $item->subtotal,
            ])
        ];
    }
}