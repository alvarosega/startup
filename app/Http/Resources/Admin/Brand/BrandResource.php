<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'image_url'   => $this->image_url, // Usa el accessor del modelo
            'is_active'   => $this->is_active,
            'is_featured' => $this->is_featured,
            'provider'    => $this->whenLoaded('provider', fn() => [
                'id' => $this->provider->id,
                'name' => $this->provider->company_name
            ]),
            'category'    => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name
            ]),
        ];
    }
}