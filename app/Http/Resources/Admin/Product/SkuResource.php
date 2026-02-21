<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SkuResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'price' => (float) $this->base_price,
            'weight' => (float) $this->weight,
            'conversion_factor' => (float) $this->conversion_factor,
            'is_active' => (bool) $this->is_active,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
        ];
    }
}
