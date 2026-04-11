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
        
        // PROTECCIÓN DE INTEGRIDAD: Si el SKU o Producto desaparecen, evitamos el crash
        $skuName = $this->sku?->name ?? 'Hardware no disponible';
        $productName = $this->sku?->product?->name ?? 'Catálogo';
        $brandName = $this->sku?->product?->brand?->name ?? 'Digital Unit';
    
        return [
            'id'           => (string) $this->id,
            'sku_id'       => (string) $this->sku_id,
            'is_bundle'    => $isBundle,
            'name'         => $isBundle 
                ? (string) ($this->bundle?->name ?? 'Pack Personalizado') 
                : trim("{$productName} {$skuName}"),
            'brand_name'   => $brandName,
            'image'        => $isBundle
                ? ($this->bundle?->image_path ? asset('storage/' . $this->bundle->image_path) : asset('assets/img/bundle_placeholder.webp'))
                : ($this->sku?->image_path ? asset('storage/' . $this->sku->image_path) : asset('assets/img/sku_placeholder.png')),
            'unit_price'   => (float) ($price->final_price ?? 0),
            'list_price'   => (float) ($price->list_price ?? 0),
            'quantity'     => (int) $this->quantity,
            'max_stock'    => (int) ($this->max_stock ?? 0),
            'subtotal'     => (float) ($this->quantity * ($price->final_price ?? 0)),
            'line_savings' => (float) ((($price->list_price ?? 0) - ($price->final_price ?? 0)) * $this->quantity),
            'price_label'  => strtoupper($price->type ?? 'REGULAR'),
            'components'   => $isBundle ? $this->bundle?->skus->map(fn($s) => [
                'qty'  => (int) ($s->pivot->quantity ?? 1),
                'name' => $s->name
            ]) : null,
            'upsell'       => ($price->next_tier ?? null) ? [
                'needed_quantity' => (int) $price->next_tier['min_quantity'] - $this->quantity,
                'potential_price' => (float) $price->next_tier['final_price']
            ] : null,
        ];
    }
}