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
            'bg_color' => $this->bg_color ? '#' . ltrim((string)$this->bg_color, '#') : '#a855f7',
            'logo_url' => $this->image_path 
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) 
                : asset('assets/img/brand_placeholder.png'), // <--- UNIFICADO Y CON ASSET
        ];
    }
}