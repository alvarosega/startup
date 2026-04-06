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
            
            // RECTIFICACIÓN: Casteo a float para compatibilidad con JS .toFixed()
            'items_subtotal' => (float) $this->items_subtotal,
            'delivery_fee'   => (float) $this->delivery_fee,
            'service_fee'    => (float) $this->service_fee,
            'total_amount'   => (float) $this->total_amount,

            'payment_method' => $this->payment_method,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'phone' => $this->customer->phone,
                // RECTIFICACIÓN: Enviamos 'name' unificado para la vista
                'full_name' => "{$this->customer->profile?->first_name} {$this->customer->profile?->last_name}",
            ]),

            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name, // Sincronizado
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) $item->subtotal, // RECTIFICACIÓN: float
            ])),
        ];
    }
}