<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ELIMINAR: $id = data_get($this->resource, 'id'); (Violación de protocolo)
        $name = data_get($this->resource, 'name');
    
        return [
            'name'        => (string) mb_convert_encoding($name ?? '', 'UTF-8', 'UTF-8'),
            'slug'        => (string) data_get($this->resource, 'slug'),
            'description' => data_get($this->resource, 'description'),
            'bg_color'    => '#' . ltrim((string) (data_get($this->resource, 'bg_color') ?? '6366f1'), '#'),
            
            // Verificación de existencia física del path
            'image_url'   => data_get($this->resource, 'image_path') 
                ? asset('storage/' . data_get($this->resource, 'image_path')) 
                : asset('assets/img/category_placeholder.png'),
    
            'seo' => [
                'title'       => data_get($this->resource, 'seo.title') ?? $name,
                'description' => data_get($this->resource, 'seo.description'),
            ],
            
            'subcategories' => self::collection(
                data_get($this->resource, 'subcategories', [])
            ),
        ];
    }
}