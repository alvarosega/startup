<?php

declare(strict_types=1);

namespace App\DTOs\Admin\RetailMedia;

final readonly class PlacementData
{
    public function __construct(
        public string $name,
        public string $code,
        public int $maxItems,
        public bool $isActive
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            code: strtoupper((string) $validated['code']),
            maxItems: (int) $validated['max_items'],
            isActive: (bool) $validated['is_active']
        );
    }
}