<?php

namespace App\DTOs\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * VEHÍCULO DE DATOS: CATEGORÍA
 * Inmutable, tipado estricto y sin duplicidad de parámetros.
 */
readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $marketZoneId,
        public ?UploadedFile $icon,
        public ?string $bgColor,
        public ?string $parentId,
        public ?string $externalCode,
        public ?string $description,
        public ?string $slug,
        public ?string $seoTitle,
        public ?string $seoDescription, // ÚNICA DEFINICIÓN
        public ?string $taxClassification,
        public bool $requiresAgeCheck,
        public bool $isActive,
        public bool $isFeatured,
        public ?UploadedFile $image,
        public array $children = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            marketZoneId: $request->validated('market_zone_id'),
            icon: $request->file('icon'),
            bgColor: $request->validated('bg_color'),
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
            children: $request->input('children', []),
        );
    }

    /**
     * Convierte el DTO a un array plano para persistencia masiva si es necesario.
     */
    public function toArray(): array
    {
        return [
            'name'               => $this->name,
            'market_zone_id'     => $this->marketZoneId,
            'parent_id'          => $this->parentId,
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