<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // resolve() aplana la estructura para que Vue reciba un array directo
        $items = CartItemResource::collection($this->whenLoaded('items'))->resolve();
        
        $itemsCollect = collect($items);

        return [
            'id'           => $this->id,
            'branch_id'    => $this->branch_id,
            // Podrías incluir el nombre de la sucursal si cargas la relación
            'branch_name'  => $this->whenLoaded('branch', fn() => $this->branch->name),
            'items'        => $items,
            'total_items'  => (int) $itemsCollect->sum('quantity'),
            'total_price'  => (float) $itemsCollect->sum('subtotal'),
            // Útil para que el frontend sepa si puede proceder al pago
            'can_checkout' => $itemsCollect->every('has_stock') && $itemsCollect->count() > 0,
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}