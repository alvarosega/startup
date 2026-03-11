<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        $items = CartItemResource::collection($this->whenLoaded('items'))->resolve();
        $itemsCollect = collect($items);

        return [
            'id'            => (string) $this->id,
            'branch_id'     => (string) $this->branch_id,
            'items'         => $items,
            
            // Las subimos a la raíz para Index.vue
            'total_items'   => (int) $itemsCollect->sum('quantity'),
            'total_price'   => (float) $itemsCollect->sum('subtotal'),
            'total_savings' => (float) $itemsCollect->sum('line_savings'),
            
            'can_checkout'  => $itemsCollect->every('has_stock') && $itemsCollect->count() > 0,
            'last_activity' => $this->updated_at->diffForHumans(),
        ];
    }
}