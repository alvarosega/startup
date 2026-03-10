<?php

namespace App\DTOs\Admin\MarketZone;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class MarketZoneData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $hex_color,
        public ?string $svg_id,
        public ?string $description,
        public bool $is_active
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            slug: Str::slug((string) $request->validated('name')),
            hex_color: $request->validated('hex_color') ?? '#3b82f6',
            svg_id: $request->validated('svg_id'),
            description: $request->validated('description'),
            is_active: $request->boolean('is_active', true)
        );
    }
}