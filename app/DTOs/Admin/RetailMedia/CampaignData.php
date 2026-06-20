<?php

declare(strict_types=1);

namespace App\DTOs\Admin\RetailMedia;

use Carbon\Carbon;

final readonly class CampaignData
{
    public function __construct(
        public string $providerId,
        public string $name,
        public string $type,
        public ?Carbon $startsAt,
        public ?Carbon $endsAt,
        public bool $isActive
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            providerId: (string) $validated['provider_id'],
            name: (string) $validated['name'],
            type: (string) $validated['type'],
            startsAt: $validated['starts_at'] ? Carbon::parse($validated['starts_at']) : null,
            endsAt: $validated['ends_at'] ? Carbon::parse($validated['ends_at']) : null,
            isActive: (bool) $validated['is_active']
        );
    }
}