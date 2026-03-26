<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // IDENTIDAD (UUIDv7)
            'id'           => (string) $this->id,
            
            // DATOS BÁSICOS SANITIZADOS
            'name'         => $this->sanitizeUtf8((string) $this->name),
            'slug'         => (string) $this->slug,
            'description'  => $this->sanitizeUtf8($this->description),
            
            // RELACIONES (IDs)
            'brand_id'     => (string) $this->brand_id,
            'category_id'  => (string) $this->category_id,
            
            // MULTIMEDIA FÍSICA
            'image_url'    => $this->image_path 
                                ? Storage::disk('public')->url($this->image_path) 
                                : asset('assets/img/placeholders/product-default.png'),
            
            // FLAGS OPERACIONALES (Casteo Estricto)
            'is_active'    => (bool) $this->is_active,
            'is_alcoholic' => (bool) $this->is_alcoholic,
            'sort_order'   => (int) $this->sort_order,
            
            // CONTADORES PARA DASHBOARD
            'skus_count'   => (int) ($this->skus_count ?? 0),

            // RELACIONES CARGADAS (Solo mediante with() en el Action/Controller)
            'skus'         => SkuResource::collection($this->whenLoaded('skus')),
            
            'brand'        => $this->whenLoaded('brand', fn() => [
                'id'   => (string) $this->brand->id,
                'name' => $this->sanitizeUtf8((string) $this->brand->name)
            ]),
            
            'category'     => $this->whenLoaded('category', fn() => [
                'id'   => (string) $this->category->id,
                'name' => $this->sanitizeUtf8((string) $this->category->name)
            ]),
        ];
    }

    /**
     * DoD v2.0: Protocolo Anti-Corrupción UTF-8.
     * Blindaje contra caracteres mal formados que rompen el JSON en el cliente.
     */
    private function sanitizeUtf8(?string $str): ?string
    {
        if (is_null($str) || $str === '') {
            return null;
        }

        // Si no es UTF-8 válido, forzamos la conversión desde ISO-8859-1 (común en importaciones legacy)
        if (!mb_check_encoding($str, 'UTF-8')) {
            return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }

        return $str;
    }
}