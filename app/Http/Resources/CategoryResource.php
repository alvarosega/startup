<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'external_code' => $this->external_code,
            'description' => $this->description,
            'image_url' => $this->image_url, // Accessor del modelo
            
            // Config
            'tax_classification' => $this->tax_classification,
            'requires_age_check' => (bool) $this->requires_age_check,
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            
            // SEO
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            
            // Recursividad ligera (solo nombre del padre para listas)
            'parent_name' => $this->whenLoaded('parent', fn() => $this->parent->name),
        ];
    }
}