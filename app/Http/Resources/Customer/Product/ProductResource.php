<?php

namespace App\Http\Resources\Customer\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name'      => $this->name,
            'slug'      => $this->slug,
            'image_url' => $this->image_url,
            'is_out_of_stock' => (bool) $this->is_out_of_stock,
        ];
    }
}