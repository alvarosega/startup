<?php

namespace App\Http\Resources\Admin\Brand;

use App\Http\Resources\Admin\MarketZone\MarketZoneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'parent_id'   => (string) $this->parent_id,
            'name'        => mb_convert_encoding($this->name, 'UTF-8'),
            'slug'        => (string) $this->slug,
            'image_url'   => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'website'     => (string) $this->website,
            'description' => (string) $this->description,
            
            // Estado
            'is_active'   => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'sort_order'  => (int) $this->sort_order,

            // Relaciones Críticas
            'provider'     => $this->whenLoaded('provider', fn() => [
                'id' => $this->provider->id,
                'name' => $this->provider->commercial_name
            ]),
            'category'     => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name
            ]),
            'market_zones' => MarketZoneResource::collection($this->whenLoaded('marketZones')),
        ];
    }
}