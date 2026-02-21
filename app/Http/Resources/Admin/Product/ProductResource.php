<?php
namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'is_active' => (bool)$this->is_active,
            'is_alcoholic' => (bool)$this->is_alcoholic,
            'skus' => SkuResource::collection($this->whenLoaded('skus')),
            'brand' => $this->whenLoaded('brand', fn() => ['id' => $this->brand->id, 'name' => $this->brand->name]),
            'category' => $this->whenLoaded('category', fn() => ['id' => $this->category->id, 'name' => $this->category->name]),
        ];
    }
}