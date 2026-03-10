<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SkuResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => (string) $this->id,
            'name'              => $this->sanitizeUtf8($this->name),
            'code'              => (string) $this->code,
            
            // LA LEY: Sincronización con base_price (DB y Vue)
            'base_price'        => (float) $this->base_price,
            'weight'            => (float) $this->weight,
            'conversion_factor' => (float) $this->conversion_factor,
            
            'is_active'         => (bool) $this->is_active,
            
            'image_url'         => $this->image_path 
                ? Storage::disk('public')->url($this->image_path) 
                : asset('assets/img/placeholder.png'),
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}