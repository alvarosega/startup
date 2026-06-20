<?php

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandHeroResource extends JsonResource
{
    public function toArray($request): array
    {
        // Generamos un sufijo de versión basado en la última actualización
        $version = $this->updated_at?->timestamp ?? time();

        return [
            'id'            => (string) $this->id,
            'image_desktop' => $this->image_desktop_path 
                ? Storage::disk('public')->url($this->image_desktop_path) . "?v={$version}" 
                : '/assets/img/brand_banner_placeholder.jpg',
            'image_mobile'  => $this->image_mobile_path 
                ? Storage::disk('public')->url($this->image_mobile_path) . "?v={$version}" 
                : '/assets/img/brand_banner_placeholder.jpg',
            'brand' => [
                'name' => $this->brand->name ?? '',
                'slug' => $this->brand->slug ?? '',
            ]
        ];
    }
}
