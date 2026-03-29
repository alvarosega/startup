<?php

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandNavResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => (string) $this->id,
            'name'     => (string) $this->name,
            'slug'     => (string) $this->slug,
            'logo_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) 
                : '/assets/img/brand_placeholder.jpg',
        ];
    }
}