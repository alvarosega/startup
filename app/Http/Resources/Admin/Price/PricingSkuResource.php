<?php

namespace App\Http\Resources\Admin\Price;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => (string) $this->id,
            'name' => $this->sanitizeUtf8($this->name),
            'code' => (string) $this->code,
            
            // LA LEY: Casteo estricto para evitar MathException en el Frontend
            'base_price' => (float) $this->base_price, 
            
            // PROTECCIÓN DE DATOS: Transformamos cada grupo usando el Resource específico
            'prices_matrix' => $this->prices->groupBy('branch_id')->map(function ($items) {
                return PriceResource::collection($items);
            }),
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}