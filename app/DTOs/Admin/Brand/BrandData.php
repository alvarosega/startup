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
        return new self(
            name: (string) $request->validated('name'),
            slug: (string) $request->validated('slug'),
            provider_id: (string) $request->validated('provider_id'),
            category_id: (string) $request->validated('category_id'),
            parent_id: $request->validated('parent_id') ?: null,
            website: $request->validated('website'),
            description: $request->validated('description'),
            is_active: $request->boolean('is_active'),
            is_featured: $request->boolean('is_featured'),
            bg_color: $request->validated('bg_color'),
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