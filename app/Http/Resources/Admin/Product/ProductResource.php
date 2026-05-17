<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Admin\Sku\SkuResource; // IMPORTACIÓN OBLIGATORIA AÑADIDA

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // IDENTIDAD
            'id'           => (string) $this->id,
            
            // DATOS BÁSICOS
            'name'         => $this->sanitizeUtf8((string) $this->name),
            'slug'         => (string) $this->slug,
            'description'  => $this->sanitizeUtf8($this->description),
            
            // RELACIONES (IDs foráneos)
            'brand_id'     => $this->brand_id ? (string) $this->brand_id : null,
            'category_id'  => $this->category_id ? (string) $this->category_id : null,
            
            // MULTIMEDIA FÍSICA
            'image_url'    => $this->image_path 
                                ? Storage::disk('public')->url($this->image_path) 
                                : asset('assets/img/placeholders/product-default.png'),
            
            // FLAGS OPERACIONALES
            'is_active'    => (bool) $this->is_active,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            'sort_order'   => (int) $this->sort_order,
            
            // CONTADORES PARA DASHBOARD
            'skus_count'   => (int) ($this->skus_count ?? 0),

            // RELACIONES CARGADAS (Blindadas contra nulos)
            'skus'         => SkuResource::collection($this->whenLoaded('skus')),
            
            'brand'        => $this->whenLoaded('brand', fn() => $this->brand ? [
                'id'   => (string) $this->brand->id,
                'name' => $this->sanitizeUtf8((string) $this->brand->name)
            ] : null),
            
            'category'     => $this->whenLoaded('category', fn() => $this->category ? [
                'id'   => (string) $this->category->id,
                'name' => $this->sanitizeUtf8((string) $this->category->name)
            ] : null),
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (is_null($str) || $str === '') {
            return null;
        }

        if (!mb_check_encoding($str, 'UTF-8')) {
            return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }

        return $str;
    }
}