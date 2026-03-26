<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Delegamos el detalle de cada línea al CartItemResource
        $items = CartItemResource::collection($this->items)->resolve();
        
        $totalPrice = collect($items)->sum('subtotal');
        $totalSavings = collect($items)->sum('line_savings');

        return [
            'id'            => (string) $this->id,
            'items'         => $items,
            'total_items'   => (int) collect($items)->sum('quantity'),
            'total_price'   => (float) $totalPrice,
            'total_savings' => (float) $totalSavings,
        ];
    }
}