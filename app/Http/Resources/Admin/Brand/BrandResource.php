<?php

namespace App\Http\Resources\Admin\Brand;

use App\Http\Resources\Admin\MarketZone\MarketZoneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     * * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            // Identidad Inmutable (UUIDv7)
            'id'            => (string) $this->id,
            'parent_id'     => (string) $this->parent_id,
            'name'          => mb_convert_encoding($this->name, 'UTF-8'),
            'slug'          => (string) $this->slug,
            
            // Assets y Contenido
            'image_url'     => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'website'       => (string) $this->website,
            'description'   => (string) $this->description,
            
            // Flags de Estado y Optimización
            'is_active'     => (bool) $this->is_active,
            'is_featured'   => (bool) $this->is_featured,
            'sort_order'    => (int) $this->sort_order,

            /**
             * LEY DE APLANAMIENTO (Para la Vista Index)
             * Estas propiedades permiten que Vue acceda a brand.provider_name
             * directamente sin navegar por objetos anidados, mejorando el rendimiento.
             */
            'provider_name' => $this->provider?->commercial_name ?? 'SIN PROVEEDOR',
            'category_name' => $this->category?->name ?? 'SIN CATEGORÍA',
            
            /**
             * Lógica Estratégica M:N para visualización
             * Si la marca está en múltiples zonas, mostramos la primera + contador.
             */
            'market_zone'   => $this->getMarketZoneLabel(),

            // Relaciones Críticas (Carga bajo demanda)
            'provider'      => $this->whenLoaded('provider', fn() => [
                'id'   => $this->provider->id,
                'name' => $this->provider->commercial_name
            ]),
            'category'      => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name
            ]),
            'market_zones'  => MarketZoneResource::collection($this->whenLoaded('marketZones')),
        ];
    }

    /**
     * Calcula la etiqueta visual para las zonas de mercado.
     */
    private function getMarketZoneLabel(): string
    {
        if (!$this->relationLoaded('marketZones') || $this->marketZones->isEmpty()) {
            return 'SIN ZONA';
        }

        $firstZone = $this->marketZones->first()->name;
        $extraCount = $this->marketZones->count() - 1;

        return $extraCount > 0 
            ? "{$firstZone} +{$extraCount}" 
            : $firstZone;
    }
}