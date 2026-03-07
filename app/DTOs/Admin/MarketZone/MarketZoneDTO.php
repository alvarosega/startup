<?php

namespace App\DTOs\Admin\MarketZone;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class MarketZoneData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $hexColor,
        public ?string $svgId,
        public ?string $description,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug') ?? Str::slug($request->validated('name')),
            hexColor: $request->validated('hex_color', '#CCCCCC'),
            svgId: $request->validated('svg_id'),
            description: $request->validated('description'),
            isActive: $request->boolean('is_active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'slug'        => $this->slug,
            'hex_color'   => $this->hexColor,
            'svg_id'      => $this->svgId,
            'description' => $this->description,
            'is_active'   => $this->isActive,
        ];
    }
}