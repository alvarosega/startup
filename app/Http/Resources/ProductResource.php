<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {    // DEBUG
        \Log::info('ProductResource data:', [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'has_brand' => $this->relationLoaded('brand'),
            'has_category' => $this->relationLoaded('category'),
            'has_skus' => $this->relationLoaded('skus'),
            'skus_count' => $this->skus ? count($this->skus) : 0,
        ]);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'is_active' => (bool) $this->is_active,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            'skus_count' => $this->skus_count ?? 0,
            
            // Relaciones - asegurar que se pasen aunque estén vacías
            'brand' => $this->whenLoaded('brand', function () {
                return $this->brand;
            }),
            
            'category' => $this->whenLoaded('category', function () {
                return $this->category;
            }),
            
            'skus' => $this->whenLoaded('skus', function () {
                return SkuResource::collection($this->skus);
            }, []), // Valor por defecto array vacío
        ];
    }
}