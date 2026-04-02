<?php

namespace App\Http\Resources\Customer\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,
            'name'       => (string) $this->name, // Verifica que en la DB el campo sea 'name'
            'brand_name' => (string) ($this->brand?->name ?? 'CyberMarket'),
            'image'      => $this->image_path 
                ? asset('storage/' . $this->image_path) 
                : asset('assets/img/product_placeholder.png'), // RUTA ESTÁNDAR
            'has_stock'  => (bool) ($this->skus_count > 0),
        ];
    }
}