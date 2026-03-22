<?php

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => mb_convert_encoding($this->name, 'UTF-8', 'UTF-8'),
            'type' => (string) $this->type,
            'provider' => $this->provider->commercial_name ?? 'N/A',
            'dates' => [
                'start' => $this->starts_at?->format('Y-m-d'),
                'end' => $this->ends_at?->format('Y-m-d'),
            ],
            'is_active' => (bool) $this->is_active,
        ];
    }
}