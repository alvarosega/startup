<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            'provider_id'   => (string) $this->provider_id,
            'provider_name' => $this->relationLoaded('provider') ? mb_toUpperCase((string) $this->provider->company_name) : null,
            'name'          => mb_toUpperCase((string) $this->name),
            'type'          => (string) $this->type,
            'starts_at'     => $this->starts_at?->format('Y-m-d H:i:s'),
            'ends_at'       => $this->ends_at?->format('Y-m-d H:i:s'),
            'is_active'     => (bool) $this->is_active,
        ];
    }
}