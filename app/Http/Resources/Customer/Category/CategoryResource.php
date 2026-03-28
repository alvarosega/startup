<?php

declare(strict_types=1);

namespace App\Http\Resources\Customer\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RESOURCE POLIMÓRFICO: Maneja Modelos, DTOs y Arreglos de Caché.
 */
class CategoryResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     * Blindado con data_get para evitar errores de tipo "Attempt to read property on array".
     */
    public function toArray(Request $request): array
    {
        // 1. Extracción segura de datos base
        $id = data_get($this->resource, 'id');
        $name = data_get($this->resource, 'name');
        $slug = data_get($this->resource, 'slug');
        $bgColor = data_get($this->resource, 'bg_color');
        $imagePath = data_get($this->resource, 'image_path') ?? data_get($this->resource, 'image');

        return [
            'id'          => (string) $id,
            'name'        => (string) mb_convert_encoding($name ?? '', 'UTF-8', 'UTF-8'),
            'slug'        => (string) $slug,
            'description' => data_get($this->resource, 'description') 
                             ? (string) mb_convert_encoding(data_get($this->resource, 'description'), 'UTF-8', 'UTF-8') 
                             : null,
            
            // Lógica de Centella: Garantiza siempre un HEX válido para el frontend
            'bg_color' => '#' . ltrim((string) ($bgColor ?? '6366f1'), '#'),
            
            // Lógica de Imagen: Prioriza asset de storage o placeholder absoluto
            'image_url'   => $imagePath 
                             ? asset('storage/' . $imagePath) 
                             : asset('assets/img/category_placeholder.png'),
            
            // Metadatos SEO
            'seo' => [
                'title'       => (string) mb_convert_encoding(data_get($this->resource, 'seo.title') ?? $name ?? '', 'UTF-8', 'UTF-8'),
                'description' => (string) mb_convert_encoding(data_get($this->resource, 'seo.description') ?? '', 'UTF-8', 'UTF-8'),
            ],
            
            // Mapeo de Banners (Procesa DTOs de Action)
            'banners' => array_map(fn($banner) => [
                'id'                => (string) data_get($banner, 'id'),
                'name'              => (string) data_get($banner, 'name'),
                // Usamos las llaves exactas que espera el componente de Banners
                'image_desktop_url' => data_get($banner, 'image_path') 
                    ? asset('storage/' . data_get($banner, 'image_path')) 
                    : asset('assets/img/banner_category_placeholder.png
                    '),
                'action_type'       => (string) data_get($banner, 'action_type'),
                'target'            => data_get($banner, 'target_data'), // Datos del SKU o Bundle
            ], (array) data_get($this->resource, 'banners', [])),

            // Mapeo de Subcategorías (Blindado para Carruseles secundarios)
            'subcategories' => array_map(fn($sub) => [
                'id'       => (string) data_get($sub, 'id'),
                'name'     => (string) data_get($sub, 'name'),
                'slug'     => (string) data_get($sub, 'slug'),
                'bg_color' => (string) (data_get($sub, 'bg_color') ?? '#6366f1'),
                'image'    => data_get($sub, 'image_path') 
                                ? asset('storage/' . data_get($sub, 'image_path')) 
                                : asset('assets/img/category_placeholder.png'),
            ], (array) data_get($this->resource, 'subcategories', [])),
        ];
    }
}