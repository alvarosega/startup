<?php
namespace App\Http\Resources\Admin\Price;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (string) $this->id,
            'type'         => (string) $this->type,
            'list_price'   => (float) $this->list_price,
            'final_price'  => (float) $this->final_price,
            'min_quantity' => (int) $this->min_quantity,
            'priority'     => (int) $this->priority,
            'valid_from'   => $this->valid_from?->toIso8601String(),
            'valid_to'     => $this->valid_to?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
            
            // Relación con el Admin (Auditoría)
            'updater' => [
                'name' => $this->updater ? $this->sanitizeUtf8($this->updater->name) : 'SISTEMA'
            ],
        ];
    }

    private function sanitizeUtf8(?string $str): ?string
    {
        if (!$str) return null;
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}