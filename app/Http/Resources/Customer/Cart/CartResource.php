<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // El backend centraliza los agregados financieros para evitar discrepancias en el cliente
        $subtotal = $this->relationLoaded('items') 
            ? $this->items->sum(fn($item) => $item->quantity * $item->price_at_addition)
            : 0.00;

        return [
            'id'          => $this->id,
            'branch_id'   => $this->branch_id,
            'total_items' => $this->relationLoaded('items') ? $this->items->sum('quantity') : 0,
            'subtotal'    => (float) $subtotal,
            'items'       => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}