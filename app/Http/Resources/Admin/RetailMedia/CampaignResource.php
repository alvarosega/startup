<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin\RetailMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'type'          => $this->type,
            'starts_at'     => $this->starts_at?->toIso8601String(),
            'ends_at'       => $this->ends_at?->toIso8601String(),
            'is_active'     => $this->is_active,
            'provider_name' => $this->provider?->name,
        ];
    }
}