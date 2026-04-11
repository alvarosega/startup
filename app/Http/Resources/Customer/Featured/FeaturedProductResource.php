<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Featured;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeaturedProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => (string) $this->id, // RECTIFICACIÓN: Llave obligatoria para Vue :key
            'name'            => (string) $this->name,
            'slug'            => (string) $this->slug,
            'brand'           => (string) $this->brand_name,
            'image_url'       => (string) $this->image_url,
            'is_out_of_stock' => (bool) $this->is_out_of_stock,
        ];
    }
}