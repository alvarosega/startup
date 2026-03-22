<?php

namespace App\DTOs\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class BrandData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $provider_id,
        public string $category_id,
        public array $market_zone_ids, // Corregido: M:N
        public ?string $parent_id,    // Corregido: Sub-marcas
        public ?string $website,
        public ?string $description,
        public bool $is_active,
        public bool $is_featured,
        public int $sort_order,
        public ?UploadedFile $image
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            slug: (string) $request->validated('slug'),
            provider_id: (string) $request->validated('provider_id'),
            category_id: (string) $request->validated('category_id'),
            market_zone_ids: (array) $request->validated('market_zone_ids', []),
            parent_id: $request->validated('parent_id'),
            website: $request->validated('website'),
            description: $request->validated('description'),
            is_active: $request->boolean('is_active', true),
            is_featured: $request->boolean('is_featured', false),
            sort_order: (int) $request->validated('sort_order', 0),
            image: $request->file('image')
        );
    }
}