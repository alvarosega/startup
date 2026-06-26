<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

readonly class BrandData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $providerId,
        public string $categoryId,
        public ?string $parentId,
        public ?string $website,
        public ?string $description,
        public bool $isActive,
        public bool $isFeatured,
        public ?string $bgColor,
        public ?UploadedFile $image
    ) {}

    /**
     * RECTIFICACIÓN: Typehint modificado a FormRequest resolviendo el error fatal de invocación de métodos dinámicos.
     */
    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            slug: (string) $validated['slug'],
            providerId: (string) $validated['provider_id'],
            categoryId: (string) $validated['category_id'],
            parentId: $validated['parent_id'] ?? null,
            website: $validated['website'] ?? null,
            description: $validated['description'] ?? null,
            isActive: (bool) $request->boolean('is_active'),
            isFeatured: (bool) $request->boolean('is_featured'),
            bgColor: $validated['bg_color'] ?? null,
            image: $request->file('image')
        );
    }

    /**
     * Aplana las propiedades a estructuras de matrices asociativas listas para persistencia en Eloquent.
     */
    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'slug'        => $this->slug,
            'provider_id' => $this->providerId,
            'category_id' => $this->categoryId,
            'parent_id'   => $this->parentId,
            'website'     => $this->website,
            'description' => $this->description,
            'is_active'   => $this->isActive,
            'is_featured' => $this->isFeatured,
            'bg_color'    => $this->bgColor,
        ];
    }
}