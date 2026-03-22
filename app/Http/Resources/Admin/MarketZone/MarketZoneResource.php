<?php

namespace App\Http\Resources\Admin\MarketZone;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'name'        => mb_convert_encoding($this->name, 'UTF-8'),
            'slug'        => (string) $this->slug,
            'hex_color'   => (string) $this->hex_color,
            'svg_id'      => (string) $this->svg_id,
            'description' => (string) $this->description,
            'is_active'   => (bool) $this->is_active,
            'brands_count'=> $this->whenCounted('brands'),
        ];
    }
}