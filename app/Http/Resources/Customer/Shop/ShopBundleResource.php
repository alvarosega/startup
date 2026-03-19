<?php

namespace App\Http\Resources\Customer\Shop;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopBundleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => (string) $this->id,
            'name'           => $this->purify($this->name),
            'slug'           => (string) $this->slug,
            'description'    => $this->purify($this->description),
            'image_path'     => (string) $this->image_path,
            'fixed_price'    => $this->fixed_price ? (float) $this->fixed_price : null,
            'is_editable'    => (bool) $this->is_editable,
            'ends_at'        => $this->ends_at ? $this->ends_at->toIso8601String() : null,
            
            // --- CRÍTICO: Información de Stock calculada en el Action ---
            'virtual_stock'  => (int) ($this->virtual_stock ?? 0),
            'stock_status'   => (string) ($this->stock_status ?? 'out_of_stock'),

            // Sincronización con la relación 'skus'
            'items' => $this->whenLoaded('skus', function() {
                return $this->skus->map(fn($sku) => [
                    'id'         => (string) $sku->id,
                    'image_url'  => (string) ($sku->image_url ?? $sku->image_path),
                ]);
            }),
        ];
    }

    private function purify(?string $str): string {
        if (!$str) return '';
        return mb_check_encoding($str, 'UTF-8') ? $str : mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }
}