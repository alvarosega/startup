<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Category;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public ?string $parent_id,
        public ?string $external_code,
        public ?string $tax_classification,
        public bool $requires_age_check,
        public bool $is_active,
        public bool $is_featured,
        public ?string $bg_color,
        public ?string $description,
        public ?string $seo_title,
        public ?string $seo_description,
        public ?UploadedFile $image,
        public ?UploadedFile $icon
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            slug: $validated['slug'] ?? null,
            parent_id: $validated['parent_id'] ?? null,
            external_code: $validated['external_code'] ?? null,
            tax_classification: $validated['tax_classification'] ?? null,
            requires_age_check: $request->boolean('requires_age_check'),
            is_active: $request->boolean('is_active', true),
            is_featured: $request->boolean('is_featured'),
            bg_color: $validated['bg_color'] ?? null,
            description: $validated['description'] ?? null,
            seo_title: $validated['seo_title'] ?? null,
            seo_description: $validated['seo_description'] ?? null,
            image: $request->file('image'),
            icon: $request->file('icon')
        );
    }

    public function toArray(): array
    {
        return [
            'name'               => $this->name,
            'slug'               => $this->slug,
            'parent_id'          => $this->parent_id,
            'external_code'      => $this->external_code,
            'tax_classification' => $this->tax_classification,
            'requires_age_check' => $this->requires_age_check,
            'is_active'          => $this->is_active,
            'is_featured'        => $this->is_featured,
            'bg_color'           => $this->bg_color,
            'description'        => $this->description,
            'seo_title'          => $this->seo_title,
            'seo_description'    => $this->seo_description,
        ];
    }
}