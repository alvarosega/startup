<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $skuLoaded = $this->relationLoaded('sku');
        $priceData = $skuLoaded ? $this->sku->resolved_price : null;

        // Replicación exacta del contrato de escala para consistencia de tarjeta
        $upsell = null;
        if ($priceData && isset($priceData->next_tier)) {
            $nextTier = $priceData->next_tier;
            $upsell = [
                'next_qty'   => (int) ($nextTier['min_quantity'] ?? 0),
                'next_price' => (float) ($nextTier['final_price'] ?? 0),
                'needed'     => (int) max(0, (int)$nextTier['min_quantity'] - $this->quantity),
            ];
        }

        return [
            'id'                => $this->id,
            'sku_id'            => $this->sku_id,
            'quantity'          => $this->quantity,
            'unit_price'        => (float) $this->price_at_addition, // Alíat para consistencia en Vue
            'list_price'        => $priceData ? (float) ($priceData->list_price ?? $this->price_at_addition) : (float) $this->price_at_addition,
            'subtotal'          => (float) ($this->quantity * $this->price_at_addition),
            'upsell'            => $upsell,
            'name'              => $this->whenLoaded('sku', fn() => $this->sku->name),
            'code'              => $this->whenLoaded('sku', fn() => $this->sku->code),
            'image_url'         => $this->whenLoaded('sku', fn() => $this->sku->image_path),
        ];
    }
}