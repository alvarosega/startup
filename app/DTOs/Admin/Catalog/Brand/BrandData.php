<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class BrandData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $provider_id,
        public string $category_id,
        public ?string $parent_id,
        public ?string $website,
        public ?string $description,
        public bool $is_active,
        public bool $is_featured,
        public ?string $bg_color,
        public ?UploadedFile $image
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            slug: (string) $validated['slug'],
            provider_id: (string) $validated['provider_id'],
            category_id: (string) $validated['category_id'],
            parent_id: $validated['parent_id'] ?? null,
            website: $validated['website'] ?? null,
            description: $validated['description'] ?? null,
            is_active: $request->boolean('is_active'),
            is_featured: $request->boolean('is_featured'),
            bg_color: $validated['bg_color'] ?? null,
            image: $request->file('image')
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'slug'        => $this->slug,
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'parent_id'   => $this->parent_id,
            'website'     => $this->website,
            'description' => $this->description,
            'is_active'   => $this->is_active,
            'is_featured' => $this->is_featured,
            'bg_color'    => $this->bg_color,
        ];
    }
}