<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'fixed_price' => $this->fixed_price,
            'is_active' => $this->is_active,
            'image_path' => $this->image_path,
            // Esto es vital para el formulario de Edición:
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'sku_id' => $sku->id,
                    'quantity' => $sku->pivot->quantity
                ]);
            }),
        ];
    }
}