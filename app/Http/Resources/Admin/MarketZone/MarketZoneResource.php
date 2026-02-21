<?php

namespace App\Http\Resources\Admin\MarketZone;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketZoneResource extends JsonResource
{
    public function resolve($request = null)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hex_color' => $this->hex_color,
            'svg_id' => $this->svg_id,
            'description' => $this->description,
            'categories_count' => $this->categories_count ?? 0,
            // Solo enviamos los IDs para que el formulario de Vue los reconozca
            'categories' => $this->whenLoaded('categories', function() {
                return $this->categories->map(fn($c) => [
                    'id' => $c->id, 
                    'name' => $c->name
                ]);
            }),
        ];
    }
}