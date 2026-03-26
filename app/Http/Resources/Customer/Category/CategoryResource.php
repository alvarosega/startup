<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\DTOs\Customer\Category\CategoryPageDTO $resource
 */
class CategoryResource extends JsonResource
{
    /**
     * Transforma el DTO inmutable en una respuesta JSON segura.
     * Prioridad: Tipado estricto y sanitización de strings.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->resource->id,
            'name'        => (string) mb_convert_encoding($this->resource->name, 'UTF-8', 'UTF-8'),
            'slug'        => (string) $this->resource->slug,
            'description' => $this->resource->description 
                             ? (string) mb_convert_encoding($this->resource->description, 'UTF-8', 'UTF-8') 
                             : null,
            'image_url'   => $this->resource->image_path 
                             ? asset('storage/' . $this->resource->image_path) 
                             : null,
            
            // SEO Metadata
            'seo' => [
                'title'       => (string) mb_convert_encoding($this->resource->seo['title'] ?? '', 'UTF-8', 'UTF-8'),
                'description' => (string) mb_convert_encoding($this->resource->seo['description'] ?? '', 'UTF-8', 'UTF-8'),
            ],

            // Mapeo de Banners (CreativeDTO)
            'banners' => array_map(fn($banner) => [
                'id'          => (string) $banner->id,
                'name'        => (string) $banner->name,
                'image_url'   => asset('storage/' . $banner->image_url),
                'action_type' => (string) $banner->action_type,
                'target'      => $banner->target_data,
                'sort_order'  => (int) $banner->sort_order,
            ], $this->resource->banners),

            // Mapeo de Subcategorías (CategorySummaryDTO)
            'subcategories' => array_map(fn($sub) => [
                'id'    => (string) $sub->id,
                'name'  => (string) $sub->name,
                'slug'  => (string) $sub->slug,
                'image' => $sub->image_path ? asset('storage/' . $sub->image_path) : null,
            ], $this->resource->subcategories),
        ];
    }
}