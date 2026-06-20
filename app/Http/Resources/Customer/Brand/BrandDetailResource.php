<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'name'        => (string) $this->name,
            'slug'        => (string) $this->slug,
            'description' => (string) $this->description,
            // Inyectamos el color para la línea de identidad del header
            'bg_color'    => $this->bg_color ? '#' . ltrim((string)$this->bg_color, '#') : null,
            'logo_url'    => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) 
                : '/assets/img/brand_banner_placeholder.jpg',
        ];
    }
}