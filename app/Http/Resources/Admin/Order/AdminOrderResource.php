<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'delivery_type' => $this->delivery_type,
            
            // RECTIFICACIÓN LOGÍSTICA: Exponer los PINs
            'pickup_otp'   => $this->pickup_otp,
            'delivery_otp' => $this->delivery_otp,

            'items_subtotal' => (float) $this->items_subtotal,
            'delivery_fee'   => (float) $this->delivery_fee,
            'service_fee'    => (float) $this->service_fee,
            'total_amount'   => (float) $this->total_amount,

            'payment_method' => $this->payment_method,

            // Relación del Cliente
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'phone' => $this->customer->phone,
                'full_name' => "{$this->customer->profile?->first_name} {$this->customer->profile?->last_name}",
            ]),

            // RECTIFICACIÓN: Añadir relación del Driver (Para la vista de despacho)
            'driver' => $this->whenLoaded('driver', fn () => [
                'name' => "{$this->driver->profile?->first_name} {$this->driver->profile?->last_name}",
                'avatar' => $this->driver->profile?->avatar_source,
            ]),

            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])),
        ];
    }
}