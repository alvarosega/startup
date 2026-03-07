<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            'name'         => $this->sanitizeUtf8($this->name),
            'slug'         => (string) $this->slug,
            'description'  => $this->sanitizeUtf8($this->description),
            'brand_id'     => (string) $this->brand_id,
            'category_id'  => (string) $this->category_id,
            'image_url'    => $this->image_path 
                                ? Storage::disk('public')->url($this->image_path) 
                                : asset('assets/img/placeholder.png'),
            'is_active'    => (bool) $this->is_active,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            
            // Relaciones
            'skus'         => SkuResource::collection($this->whenLoaded('skus')),
            'brand'        => $this->whenLoaded('brand', fn() => [
                'id'   => (string) $this->brand->id, 
                'name' => $this->sanitizeUtf8($this->brand->name)
            ]),
            'category'     => $this->whenLoaded('category', fn() => [
                'id'   => (string) $this->category->id, 
                'name' => $this->sanitizeUtf8($this->category->name)
            ]),
        ];
    }

    /**
     * DoD v2.0: Protocolo Anti-Corrupción UTF-8
     */
    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        if (!mb_check_encoding($str, 'UTF-8')) {
            return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }
        return $str;
    }
}