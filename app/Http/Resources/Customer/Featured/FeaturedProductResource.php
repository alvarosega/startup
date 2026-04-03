<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Featured;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeaturedProductResource extends JsonResource
{
    public function toArray($request): array
    {
        // El resource ahora es un pasamanos limpio del DTO sanitizado
        return [
            'name'            => $this->name,
            'slug'            => $this->slug,
            'brand'           => $this->brand_name,
            'image_url'       => $this->image_url,
            'is_out_of_stock' => (bool) $this->is_out_of_stock,
        ];
    }
}