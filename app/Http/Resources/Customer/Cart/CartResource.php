<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Se asume que el Action ya adjuntó estos totales dinámicos a la instancia del carrito.
        // Cero cálculos en esta capa.
        return [
            'id'            => (string) $this->id,
            'items'         => CartItemResource::collection($this->items),
            'total_items'   => (int) $this->calculated_total_items,
            'total_price'   => (float) $this->calculated_total_price,
            'total_savings' => (float) $this->calculated_total_savings,
        ];
    }
}