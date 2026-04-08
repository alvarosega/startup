<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // El Action GetCustomerCartAction es responsable de hidratar 'current_price_data' y 'max_stock'.
        $price = $this->current_price_data; 
    
        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $this->sku_id,
            'name'         => mb_convert_encoding($this->sku->product->name . " " . $this->sku->name, 'UTF-8', 'auto'),
            'image'        => asset('storage/' . $this->sku->image_path),
            'unit_price'   => (float) $price->final_price,
            'list_price'   => (float) $price->list_price,
            'quantity'     => (int) $this->quantity,
            'max_stock'    => (int) $this->max_stock,
            'subtotal'     => (float) ($this->quantity * $price->final_price),
            'line_savings' => (float) (($price->list_price - $price->final_price) * $this->quantity),
            'price_label'  => strtoupper($price->type),
            'upsell'       => $price->next_tier ? [
                'needed_quantity' => $price->next_tier['min_quantity'] - $this->quantity,
                'potential_price' => (float) $price->next_tier['final_price']
            ] : null,
        ];
    }
}