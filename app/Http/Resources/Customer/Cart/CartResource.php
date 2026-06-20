<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasItems = $this->relationLoaded('items');
        
        // Forzar la resolución de los ítems hijos para calcular los totales sobre el mismo set mapeado
        $resolvedItems = $hasItems 
            ? CartItemResource::collection($this->items)->resolve() 
            : [];

        $totalPrice = collect($resolvedItems)->sum('subtotal');
        $totalSavings = collect($resolvedItems)->sum('line_savings');
        $totalItems = collect($resolvedItems)->sum('quantity');

        return [
            'id'            => $this->id,
            'branch_id'     => $this->branch_id,
            'total_items'   => (int) $totalItems,
            'total_price'   => (float) $totalPrice,   // Mapeo exacto para Vue
            'total_savings' => (float) $totalSavings, // Mapeo exacto para Vue
            'items'         => $resolvedItems,        // Array plano garantizado sin mutación .data
        ];
    }
}