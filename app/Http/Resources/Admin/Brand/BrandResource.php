<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => (string) $this->id,
            'name'           => $this->sanitizeUtf8($this->name),
            'slug'           => (string) $this->slug,
            'is_active'      => (bool) $this->is_active,
            'is_featured'    => (bool) $this->is_featured,
            'image_url'      => $this->image_url, // Usa el accesor del Modelo
            
            'provider_name'  => $this->sanitizeUtf8($this->provider?->commercial_name) ?? 'S/P',
            'category_name'  => $this->sanitizeUtf8($this->category?->name) ?? 'S/C',
            'market_zone'    => $this->sanitizeUtf8($this->marketZone?->name) ?? 'GLOBAL',
            
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        
        if (!mb_check_encoding($str, 'UTF-8')) {
            return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }
        
        return $str;
    }
}