<?php

namespace App\DTOs\Admin\MarketZone;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketZoneDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $hexColor,
        public readonly string $svgId,
        public readonly ?string $description,
        public readonly array $categories = [] // <--- NUEVO
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request instanceof \Illuminate\Foundation\Http\FormRequest ? $request->validated() : $request->all();

        return new self(
            name: $data['name'],
            slug: Str::slug($data['name']),
            hexColor: $data['hex_color'],
            svgId: $data['svg_id'],
            description: $data['description'] ?? null,
            categories: $data['categories'] ?? [] // <--- NUEVO
        );
    }
}