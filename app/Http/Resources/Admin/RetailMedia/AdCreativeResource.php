<?php

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdCreativeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->purify($this->name),
            'image_mobile' => $this->image_mobile_path,
            'image_desktop' => $this->image_desktop_path,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            
            // Relaciones mapeadas con seguridad
            'campaign' => [
                'name' => $this->purify($this->campaign->name),
                'provider' => $this->campaign->provider->commercial_name ?? 'N/A',
                'market_zone' => $this->campaign->marketZone ? [
                    'name' => $this->campaign->marketZone->name,
                    'color' => $this->campaign->marketZone->hex_color,
                ] : null,
            ],
            'placement' => [
                'name' => $this->placement->name,
                'code' => $this->placement->code,
            ],
            'sku' => [
                'id' => $this->sku->id,
                'name' => $this->purify($this->sku->name),
            ],
            'branches' => $this->branches->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
            ]),
        ];
    }

    private function purify(?string $str): string
    {
        if (!$str) return '';
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
}