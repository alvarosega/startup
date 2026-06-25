<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public ?string $parentId,
        public ?string $externalCode,
        public ?string $taxClassification,
        public bool $requiresAgeCheck,
        public bool $isActive,
        public bool $isFeatured,
        public ?string $bgColor,
        public ?string $description,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public ?UploadedFile $image,
        public ?UploadedFile $icon
    ) {}

    /**
     * Factoría estática adaptada estrictamente para acoplarse con clases de tipo FormRequest.
     */
    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            slug: $validated['slug'] ?? null,
            parentId: $validated['parent_id'] ?? null,
            externalCode: $validated['external_code'] ?? null,
            taxClassification: $validated['tax_classification'] ?? null,
            requiresAgeCheck: (bool) $request->boolean('requires_age_check'),
            isActive: (bool) $request->boolean('is_active', true),
            isFeatured: (bool) $request->boolean('is_featured'),
            bgColor: $validated['bg_color'] ?? null,
            description: $validated['description'] ?? null,
            seoTitle: $validated['seo_title'] ?? null,
            seoDescription: $validated['seo_description'] ?? null,
            image: $request->file('image'),
            icon: $request->file('icon')
        );
    }

    /**
     * Transforma las propiedades escalares a arreglos nativos para la hidratación de modelos Eloquent.
     */
    public function toArray(): array
    {
        return [
            'name'               => $this->name,
            'slug'               => $this->slug,
            'parent_id'          => $this->parentId,
            'external_code'      => $this->externalCode,
            'tax_classification' => $this->taxClassification,
            'requires_age_check' => $this->requiresAgeCheck,
            'is_active'          => $this->isActive,
            'is_featured'        => $this->isFeatured,
            'bg_color'           => $this->bgColor,
            'description'        => $this->description,
            'seo_title'          => $this->seoTitle,
            'seo_description'    => $this->seoDescription,
        ];
    }
}