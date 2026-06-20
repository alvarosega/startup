<?php

namespace App\Http\Resources\Admin\Price;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => (string) $this->id,
            'name'      => $this->sanitizeUtf8($this->name),
            'image_url' => $this->image_url, // Usando el accessor del modelo
            
            // LA LEY: Delegación estricta de responsabilidades
            'skus' => PricingSkuResource::collection($this->whenLoaded('skus')),
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}