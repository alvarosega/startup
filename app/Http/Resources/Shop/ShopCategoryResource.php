<?php

namespace App\Http\Resources\Shop;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // CONVERSIÓN OBLIGATORIA: De binario a Hex para Vue
            'id' => bin2hex($this->getRawOriginal('id')),
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => $this->image_url,
            // Recursividad sanitizada
            'children' => ShopCategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}