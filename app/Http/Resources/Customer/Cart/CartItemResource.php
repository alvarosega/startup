<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->current_price_data;
        $isBundle = (bool) $this->is_bundle;
    
        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $this->sku_id,
            'is_bundle'    => $isBundle,
            // RECTIFICACIÓN: Selección dinámica de Nombre/Imagen para evitar nulos
            'name'         => $isBundle 
                ? (string) ($this->bundle->name ?? 'Pack Personalizado') 
                : mb_convert_encoding($this->sku->product->name . " " . $this->sku->name, 'UTF-8', 'auto'),
            'image'        => $isBundle
                ? ($this->bundle->image_path ? asset('storage/' . $this->bundle->image_path) : asset('assets/img/bundle_placeholder.webp'))
                : asset('storage/' . $this->sku->image_path),
            'unit_price'   => (float) $price->final_price,
            'list_price'   => (float) $price->list_price,
            'quantity'     => (int) $this->quantity,
            'max_stock'    => (int) $this->max_stock,
            'subtotal'     => (float) ($this->quantity * $price->final_price),
            'line_savings' => (float) (($price->list_price - $price->final_price) * $this->quantity),
            'price_label'  => strtoupper($price->type),
            'components'   => $isBundle ? $this->bundle->skus->map(fn($s) => [
                'qty'  => (int) ($s->pivot->quantity ?? 1),
                'name' => $s->name
            ]) : null,
            'upsell'       => $price->next_tier ? [
                'needed_quantity' => $price->next_tier['min_quantity'] - $this->quantity,
                'potential_price' => (float) $price->next_tier['final_price']
            ] : null,
        ];
    }
}