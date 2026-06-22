<?php

declare(strict_types=1);

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\Request;

readonly class CampaignData
{
    public function __construct(
        public ?string $provider_id,
        public string $name,
        public string $type,
        public ?string $starts_at,
        public ?string $ends_at,
        public bool $is_active
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            provider_id: $validated['provider_id'] ?? null,
            name: trim((string) $validated['name']),
            type: (string) $validated['type'],
            starts_at: $validated['starts_at'] ?? null,
            ends_at: $validated['ends_at'] ?? null,
            is_active: (bool) ($validated['is_active'] ?? true)
        );
    }
}