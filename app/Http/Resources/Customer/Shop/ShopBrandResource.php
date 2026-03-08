<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShopBrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'name'      => (string) $this->name,
            'slug'      => (string) $this->slug,
            'image_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path)
                : asset('assets/img/placeholder.png'),
            'type'      => 'brand'
        ];
    }
}