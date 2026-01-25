<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'provider_id' => $this->provider_id,
            // Cargamos el objeto Provider si está disponible (eager loading)
            'provider' => $this->whenLoaded('provider', function() {
                return [
                    'id' => $this->provider->id,
                    'name' => $this->provider->commercial_name ?? $this->provider->company_name
                ];
            }),
            'manufacturer' => $this->manufacturer,
            'origin_country_code' => $this->origin_country_code,
            'tier' => $this->tier,
            'website' => $this->website,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            // Categorías simplificadas
            'categories' => $this->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            'current_categories' => $this->categories->pluck('id'), // Para el formulario
        ];
    }
}