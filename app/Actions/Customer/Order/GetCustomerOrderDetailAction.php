<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;
use Illuminate\Support\Carbon;

class GetCustomerOrderDetailAction
{
    public function execute(GetCustomerOrderDTO $dto): array
    {
        $order = Order::where('id', $dto->orderId)
            ->where('customer_id', $dto->customerId)
            ->with(['items', 'branch:id,name,address'])
            ->firstOrFail();

        $now = Carbon::now();
        $expiresAt = $order->reservation_expires_at;
        
        // Cálculo de segundos restantes (Blindaje contra negativos)
        $secondsRemaining = $expiresAt ? max(0, $now->diffInSeconds($expiresAt, false)) : 0;
        
        // RECTIFICACIÓN: Estado sincronizado con PlaceOrderAction ('pending')
        $isExpired = ($order->status === 'pending' && $secondsRemaining <= 0) || $order->status === 'expired';

        return [
            'order' => [
                'id'             => $order->id,
                'code'           => $order->code,
                'status' => $isExpired ? 'expired' : $order->status,
                'delivery_type'  => $order->delivery_type,
                'delivery_data'  => $order->delivery_data,
                'items_subtotal' => (float) $order->items_subtotal,
                'delivery_fee'   => (float) $order->delivery_fee,
                'service_fee'    => (float) $order->service_fee,
                'total_amount'   => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'created_at'     => $order->created_at->toDateTimeString(),
                'items'          => $order->items->map(fn($item) => [
                    'name'     => $item->product_name . ' ' . $item->sku_name,
                    'image'    => $item->image_snapshot ? asset('storage/' . $item->image_snapshot) : asset('assets/img/sku_placeholder.png'),
                    'quantity' => $item->quantity,
                    'price'    => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
                'branch' => $order->branch
            ],
            'delivery_otp' => $order->status === 'arrived' ? $order->delivery_otp : null,
            'payment_context' => [
                'seconds_remaining' => $secondsRemaining,
                'is_expired'        => $isExpired,
                'qr_image'          => asset('assets/img/static_qr_payment.png'), // Asset estático confirmado
                'qr_payload'        => $isExpired ? null : "PAY-ORDER-{$order->code}",
                'bank_name'         => 'BANCO UNIÓN / BCP', 
            ]
        ];
    }
}