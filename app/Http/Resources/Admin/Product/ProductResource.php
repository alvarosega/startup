<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Product;

use App\Http\Resources\Admin\Sku\SkuResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            'brand_id'     => (string) $this->brand_id,
            'category_id'  => (string) $this->category_id,
            'name'         => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'slug'         => (string) $this->slug,
            'description'  => (string) $this->description,
            'image_url'    => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'is_active'    => (bool) $this->is_active,
            'is_featured'  => (bool) $this->is_featured,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            'sort_order'   => (int) $this->sort_order,
            
            // Forzado determinista solicitado para la vista Index y Workspace
            'brand_name'    => $this->brand?->name ?? 'SIN MARCA',
            'category_name' => $this->category?->name ?? 'SIN CATEGORÍA',
            'skus_count'    => (int) ($this->skus_count ?? $this->skus()->count()),

            // Carga relacional anidada controlada
            'skus' => SkuResource::collection($this->whenLoaded('skus')),
        ];
    }
}