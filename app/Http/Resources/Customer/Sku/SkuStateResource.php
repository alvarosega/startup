<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Sku;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuStateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // IMPORTANTE: Normalizamos llaves para que coincidan con SkuCard.vue
        return [
            'id'                  => (string) $this->resource->sku_id, // Añadido ID para evitar error de validación
            'final_price'         => (float) $this->resource->final_price,
            'list_price'          => (float) $this->resource->list_price,
            'stock'               => (int) $this->resource->stock_available, // Sincronizado con 'stock'
            'can_add_more'        => (bool) $this->resource->can_add_more,
            'upsell'              => $this->resource->upsell_data, // Sincronizado con 'upsell'
            'is_active'           => (bool) $this->resource->is_active,
        ];
    }
}