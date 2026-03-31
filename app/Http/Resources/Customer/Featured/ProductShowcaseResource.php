<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Featured;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductShowcaseResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'name' => mb_convert_encoding($this['product']->name, 'UTF-8', 'UTF-8'),
                'description' => $this['product']->description,
            ],
            'skus' => $this['skus']->map(fn($sku) => $this->formatSku($sku)),
            'others_paginated' => [
                'data' => collect($this['others']->items())->map(fn($sku) => $this->formatSku($sku)),
                'next_cursor' => $this['others']->nextCursor()?->encode(),
            ]
        ];
    }

    /**
     * Normaliza el SKU para que sea compatible con SkuCard.vue
     */
    private function formatSku($sku): array
    {
        return [
            'id'          => $sku->id,
            'name'        => $sku->name,
            'brand_name'  => $sku->product->brand->name ?? 'GENERIC_ASSET',
            'image'       => $sku->image_path ? asset('storage/' . $sku->image_path) : null,
            'final_price' => (float) $sku->base_price,
            'list_price'  => (float) $sku->base_price, // Fallback si no hay descuento
            'discount_percentage' => 0,
            'stock'       => 10, // Placeholder hasta integrar inventario real
            'bg_color'    => $sku->bg_color ?? '#4ade80',
            'upsell'      => null, // Evita error de toFixed en upsell.next_price
        ];
    }
}