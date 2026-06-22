<?php

declare(strict_types=1);

namespace App\DTOs\Admin\RetailMedia;

use Illuminate\Http\Request;

readonly class PlacementData
{
    public function __construct(
        public string $name,
        public string $code,
        public int $max_items,
        public bool $is_active
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            name: trim((string) $validated['name']),
            code: mb_toUpperCase(trim((string) $validated['code'])),
            max_items: (int) ($validated['max_items'] ?? 5),
            is_active: (bool) ($validated['is_active'] ?? true)
        );
    }
}