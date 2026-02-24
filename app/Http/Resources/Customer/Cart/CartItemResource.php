<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sku = $this->sku;
        // El precio ya viene filtrado por sucursal desde el controlador
        $price = $sku->prices->first(); 
        // El stock se suma solo de los lotes de la sucursal activa
        $stock = (int) $sku->inventoryLots->sum('quantity');

        return [
            'id'         => $this->id,
            'sku_id'     => $sku->id,
            'name'       => $sku->product->name . ' (' . $sku->name . ')',
            'image'      => $sku->image_url,
            'unit_price' => (float) ($price->final_price ?? 0),
            'quantity'   => (int) $this->quantity,
            'subtotal'   => (float) ($this->quantity * ($price->final_price ?? 0)),
            'max_stock'  => $stock,
        ];
    }
}