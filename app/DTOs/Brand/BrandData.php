<?php

namespace App\DTOs\Brand;

use Illuminate\Http\UploadedFile;

class BrandData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?string $providerId,
        public readonly ?string $manufacturer,
        public readonly ?string $originCountryCode,
        public readonly string $tier,
        public readonly ?string $website,
        public readonly bool $isActive,
        public readonly bool $isFeatured,
        public readonly ?UploadedFile $image, // Archivo opcional
        public readonly array $categories,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            providerId: $request->validated('provider_id'),
            manufacturer: $request->validated('manufacturer'),
            originCountryCode: $request->validated('origin_country_code'),
            tier: $request->validated('tier'),
            website: $request->validated('website'),
            isActive: $request->boolean('is_active', true),
            isFeatured: $request->boolean('is_featured', false),
            image: $request->file('image'), // Capturamos el archivo
            categories: $request->validated('categories') ?? [],
        );
    }

    public function toArray(): array
    {
        // Nota: No incluimos 'image' ni 'categories' aquí porque se procesan aparte en el Action
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'provider_id' => $this->providerId,
            'manufacturer' => $this->manufacturer,
            'origin_country_code' => $this->originCountryCode,
            'tier' => $this->tier,
            'website' => $this->website,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ];
    }
}