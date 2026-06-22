<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Catalog\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            'parent_id'     => $this->parent_id ? (string) $this->parent_id : null,
            'name'          => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'slug'          => (string) $this->slug,
            'image_url'     => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'website'       => $this->website,
            'description'   => $this->description,
            'is_active'     => (bool) $this->is_active,
            'is_featured'   => (bool) $this->is_featured,
            'sort_order'    => (int) $this->sort_order,
            'bg_color'      => $this->bg_color ? '#' . ltrim((string) $this->bg_color, '#') : '#6366F1',
            'provider_name' => $this->provider?->commercial_name ?? 'SIN PROVEEDOR',
            'category_name' => $this->category?->name ?? 'SIN CATEGORÍA',
            
            'provider'      => $this->whenLoaded('provider', fn() => [
                'id'   => $this->provider->id,
                'name' => $this->provider->commercial_name
            ]),
            'category'      => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name
            ]),
        ];
    }
}