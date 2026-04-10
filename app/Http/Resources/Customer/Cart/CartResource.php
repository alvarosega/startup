<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            // RECTIFICACIÓN: .resolve() elimina la llave 'data' anidada
            'items'         => CartItemResource::collection($this->items)->resolve(),
            'total_items'   => (int) ($this->calculated_total_items ?? 0),
            'total_price'   => (float) ($this->calculated_total_price ?? 0),
            'total_savings' => (float) ($this->calculated_total_savings ?? 0),
        ];
    }
}