<?php

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\Request;

readonly class AdCampaignDTO
{
    public function __construct(
        public ?string $id,
        public string $provider_id,
        public ?string $market_zone_id,
        public string $name,
        public string $type,
        public ?string $starts_at,
        public ?string $ends_at,
        public bool $is_active
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->input('id'),
            provider_id: $request->input('provider_id'),
            market_zone_id: $request->input('market_zone_id'),
            name: $request->string('name')->trim(),
            type: $request->input('type', 'PAID'),
            starts_at: $request->input('starts_at'),
            ends_at: $request->input('ends_at'),
            is_active: $request->boolean('is_active', true)
        );
    }
}