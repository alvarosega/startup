<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sku = $this->sku;

        // 1. Obtención de precio por prioridad
        $price = $sku->prices
            ->where('min_quantity', '<=', $this->quantity)
            ->sortBy('priority') 
            ->first();

        // 2. Cálculo ÚNICO de stock (Físico - Reservado - Seguridad)
        $stock = (int) $sku->inventoryLots
            ->where('is_safety_stock', false)
            ->sum(function($lot) {
                return $lot->quantity - $lot->reserved_quantity;
            });

        $finalPrice = (float) ($price->final_price ?? $sku->base_price);

        return [
            'id'         => $this->id,
            'sku_id'     => $sku->id,
            'name'       => $sku->product->name . ' (' . $sku->name . ')',
            'image'      => $sku->image_url,
            'unit_price' => $finalPrice,
            'quantity'   => (int) $this->quantity,
            'subtotal'   => (float) ($this->quantity * $finalPrice),
            'max_stock'  => max(0, $stock),
            'has_stock'  => $stock >= $this->quantity,
        ];
    }
}