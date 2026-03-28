<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    /**
     * Transforma el recurso para el silo de administración.
     * Mantiene la integridad de codificación y asegura placeholders visuales.
     */
    public function toArray(Request $request): array
    {
        return [
            // Identificadores Atómicos
            'id'                 => (string) $this->id,
            'parent_id'          => $this->parent_id ? (string) $this->parent_id : null,
            
            // Datos Descriptivos (Normalización UTF-8)
            'name'               => mb_convert_encoding((string) ($this->name ?? ''), 'UTF-8', 'UTF-8'),
            'slug'               => (string) $this->slug,
            'description'        => $this->description ? mb_convert_encoding((string) $this->description, 'UTF-8', 'UTF-8') : null,
            
            // Atributos de Clasificación y Control
            'external_code'      => (string) $this->external_code,
            'tax_classification' => (string) $this->tax_classification,
            'requires_age_check' => (bool) $this->requires_age_check,
            'is_active'          => (bool) $this->is_active,
            'is_featured'        => (bool) $this->is_featured,
            'sort_order'         => (int) $this->sort_order,
            
            // Activos Visuales con Lógica de Fallback (Placeholders)
            'image_url'          => $this->image_path 
                                    ? asset('storage/' . $this->image_path) 
                                    : asset('assets/img/category_placeholder.png'),
            
            'icon_url'           => $this->icon_path 
                                    ? asset('storage/' . $this->icon_path) 
                                    : null, // Los iconos suelen ser opcionales, no requieren placeholder.

            // Identidad Visual: Garantizamos el prefijo '#' para el renderizado
            'bg_color'           => $this->bg_color ? '#' . ltrim((string) $this->bg_color, '#') : '#6366F1',
            
            // Relaciones Recursivas
            'parent'             => new CategoryResource($this->whenLoaded('parent')),
            'children_count'     => $this->whenCounted('children'),
        ];
    }
}