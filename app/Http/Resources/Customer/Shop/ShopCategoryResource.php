<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id, // UUID String
            'name'      => $this->name,
            'slug'      => $this->slug,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'type'      => 'category'
        ];
    }
}