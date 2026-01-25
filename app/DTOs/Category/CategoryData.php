<?php

namespace App\DTOs\Category;

use Illuminate\Http\UploadedFile;

class CategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $parentId,
        public readonly ?string $externalCode,
        public readonly ?string $description,
        public readonly ?string $slug,
        public readonly ?string $seoTitle,
        public readonly ?string $seoDescription,
        public readonly ?string $taxClassification,
        public readonly bool $requiresAgeCheck,
        public readonly bool $isActive,
        public readonly bool $isFeatured,
        public readonly ?UploadedFile $image,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            parentId: $request->validated('parent_id'),
            externalCode: $request->validated('external_code'),
            description: $request->validated('description'),
            slug: $request->validated('slug'),
            seoTitle: $request->validated('seo_title'),
            seoDescription: $request->validated('seo_description'),
            taxClassification: $request->validated('tax_classification'),
            requiresAgeCheck: $request->boolean('requires_age_check'),
            isActive: $request->boolean('is_active', true),
            isFeatured: $request->boolean('is_featured'),
            image: $request->file('image'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'parent_id' => $this->parentId,
            'external_code' => $this->externalCode,
            'description' => $this->description,
            'slug' => $this->slug,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'tax_classification' => $this->taxClassification,
            'requires_age_check' => $this->requiresAgeCheck,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ];
    }
}