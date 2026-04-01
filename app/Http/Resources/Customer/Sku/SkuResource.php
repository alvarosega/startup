<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Sku;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $priceData = $this->resource->resolved_price;
        
        $finalPrice = (float) ($priceData->final_price ?? 0);
        $listPrice = (float) ($priceData->list_price ?? 0);
        
        $discount = ($listPrice > $finalPrice && $listPrice > 0) 
            ? (int) round((($listPrice - $finalPrice) / $listPrice) * 100) 
            : 0;

        $upsell = null;
        if (isset($priceData->next_tier) && is_array($priceData->next_tier)) {
            $nextTier = $priceData->next_tier;
            $qtyInCart = (int) ($this->resource->quantity_in_cart ?? 0);
            $upsell = [
                'next_qty'   => (int) ($nextTier['min_quantity'] ?? 0),
                'next_price' => (float) ($nextTier['final_price'] ?? 0),
                'needed'     => (int) max(0, (int)$nextTier['min_quantity'] - $qtyInCart),
            ];
        }

        return [
            'id'                  => (string) $this->resource->id,
            'product_id' => (string) $this->product_id,
            'name'                => (string) ($this->resource->name ?? $this->resource->sku_name),
            'brand_name'          => (string) ($this->resource->brand_name ?? $this->resource->product?->brand?->name),
            'image'               => $this->resource->image_path ? asset('storage/' . $this->resource->image_path) : asset('assets/img/sku_placeholder.png'),
            'bg_color'            => $this->resource->bg_color ? '#' . ltrim((string) $this->resource->bg_color, '#') : null,
            'final_price'         => $finalPrice,
            'list_price'          => $listPrice,
            'discount_percentage' => $discount,
            'is_favorite'         => (bool) ($this->resource->is_favorite ?? false), // Inyectado por el Action
            'product_id'          => (string) $this->resource->product_id, // Necesario para toggle a nivel producto
            'stock'               => (int) ($this->resource->available_stock ?? 0),
            'upsell'              => $upsell,
            // Persistencia Cursor
            'sort_order'          => $this->resource->sort_order,
            'sorting_price'       => (float) ($this->resource->sorting_price ?? $finalPrice),
        ];
    }
}