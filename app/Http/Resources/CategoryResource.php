<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'market_zone_id' => $this->market_zone_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'external_code' => $this->external_code,
            'description' => $this->description,
            
            // Assets con URLs absolutas
            'image_url' => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) 
                : asset('assets/img/placeholder.png'),
            'icon_url' => $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null,
            'bg_color' => $this->bg_color,

            // Config & Booleans
            'tax_classification' => $this->tax_classification,
            'requires_age_check' => (bool) $this->requires_age_check,
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'sort_order' => (int) $this->sort_order,
            
            // SEO
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            
            // Relaciones Sanitizadas
            'parent_name' => $this->whenLoaded('parent', fn() => $this->parent->name),
            'zone' => $this->whenLoaded('marketZone', fn() => [
                'id' => $this->marketZone->id,
                'name' => $this->marketZone->name,
                'color' => $this->marketZone->hex_color
            ]),
        ];
    }
}