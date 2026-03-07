<?php

namespace App\DTOs\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?UploadedFile $icon,
        public ?string $bgColor,
        public ?string $externalCode,
        public ?string $description,
        public ?string $slug,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public ?string $taxClassification,
        public bool $requiresAgeCheck,
        public bool $isActive,
        public bool $isFeatured,
        public ?UploadedFile $image,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            icon: $request->file('icon'),
            bgColor: $request->validated('bg_color'),
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
            'name'               => $this->name,
            'external_code'      => $this->externalCode,
            'description'        => $this->description,
            'slug'               => $this->slug,
            'seo_title'          => $this->seoTitle,
            'seo_description'    => $this->seoDescription,
            'tax_classification' => $this->taxClassification,
            'requires_age_check' => $this->requiresAgeCheck,
            'is_active'          => $this->isActive,
            'is_featured'        => $this->isFeatured,
            'bg_color'           => $this->bgColor,
        ];
    }
}