<?php

namespace App\DTOs\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public ?string $parent_id,
        public ?string $externalCode,
        public ?string $taxClassification,
        public bool $requiresAgeCheck,
        public bool $isActive,
        public bool $isFeatured,
        public int $sortOrder,
        public ?string $bgColor,
        public ?string $description,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public ?UploadedFile $image,
        public ?UploadedFile $icon,
        public int $version = 0,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            slug: $request->validated('slug'),
            // PROTOCOLO: Convertir cadena vacía de HTML a NULL real
            parent_id: $request->validated('parent_id') ?: null,
            externalCode: $request->validated('external_code'),
            taxClassification: $request->validated('tax_classification'),
            requiresAgeCheck: $request->boolean('requires_age_check'),
            isActive: $request->boolean('is_active', true),
            isFeatured: $request->boolean('is_featured'),
            sortOrder: (int) $request->validated('sort_order', 0),
            bgColor: $request->validated('bg_color'),
            description: $request->validated('description'),
            seoTitle: $request->validated('seo_title'),
            seoDescription: $request->validated('seo_description'),
            image: $request->file('image'),
            icon: $request->file('icon'),
            version: (int) $request->validated('version', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'name'               => $this->name,
            'slug'               => $this->slug,
            'parent_id'          => $this->parent_id,
            'external_code'      => $this->externalCode,
            'tax_classification' => $this->taxClassification,
            'requires_age_check' => $this->requiresAgeCheck,
            'is_active'          => $this->isActive,
            'is_featured'        => $this->isFeatured,
            'sort_order'         => $this->sortOrder,
            'bg_color'           => $this->bgColor,
            'description'        => $this->description,
            'seo_title'          => $this->seoTitle,
            'seo_description'    => $this->seoDescription,
            'version' => $this->version,
        ];
    }
}