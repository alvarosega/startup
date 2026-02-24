<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // resolve() aquí asegura que 'items' sea un array simple, no un objeto {data: [...]}
        $items = CartItemResource::collection($this->whenLoaded('items'))->resolve();
        
        return [
            'id'           => $this->id,
            'branch_id'    => $this->branch_id,
            'items'        => $items,
            'total_items'  => (int) collect($items)->sum('quantity'),
            'total_price'  => (float) collect($items)->sum('subtotal'),
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}