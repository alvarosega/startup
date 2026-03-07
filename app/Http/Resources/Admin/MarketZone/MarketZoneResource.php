<?php

namespace App\Http\Resources\Admin\MarketZone;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            // SANITIZACIÓN EXTREMA: Forzar UTF-8 para limpiar caracteres corruptos (como la \xf3)
            'name'         => $this->sanitizeUtf8($this->name),
            'slug'         => (string) $this->slug,
            'hex_color'    => (string) $this->hex_color,
            'svg_id'       => $this->svg_id ? $this->sanitizeUtf8($this->svg_id) : null,
            'description'  => $this->description ? $this->sanitizeUtf8($this->description) : null,
            'is_active'    => (bool) $this->is_active,
            'brands_count' => (int) $this->brands_count,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Helper de seguridad para forzar conversión a UTF-8 válido
     */
    private function sanitizeUtf8(?string $string): ?string
    {
        if (!$string) return null;
        
        // Si el string no es UTF-8 válido, lo convierte desde ISO-8859-1 (Latin1)
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        
        return $string;
    }
}