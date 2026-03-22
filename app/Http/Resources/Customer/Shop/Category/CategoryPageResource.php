<?php

namespace App\Http\Resources\Customer\Shop\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use App\Http\Resources\Customer\Shop\ShopProductResource;

class CategoryPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => (string) $this['category']->id,
            'name'     => (string) mb_convert_encoding($this['category']->name, 'UTF-8', 'UTF-8'),
            'slug'     => (string) $this['category']->slug,
            'banners'  => HeroBannerResource::collection($this['banners'])->resolve(),
            // Transformación individual de cada SKU
            'products' => ShopProductResource::collection($this['products'])->resolve(),
        ];
    }
}