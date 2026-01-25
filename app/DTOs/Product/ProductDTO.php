<?php

namespace App\DTOs\Product;

use App\Http\Requests\Product\UpsertProductRequest;

class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $brand_id,
        public readonly string $category_id,
        public readonly ?string $description,
        public readonly bool $is_active,
        public readonly bool $is_alcoholic,
        public readonly ?object $image, // Archivo binario
        /** @var SkuDTO[] */
        public readonly array $skus,
    ) {}

    public static function fromRequest(UpsertProductRequest $request): self
    {
        $skus = array_map(function ($item) {
            return new SkuDTO(
                id: $item['id'] ?? null,
                name: $item['name'],
                code: $item['code'] ?? null,
                price: (float) $item['price'],
                conversion_factor: (float) ($item['conversion_factor'] ?? 1),
                weight: (float) ($item['weight'] ?? 0),
                image: $item['image'] ?? null,
            );
        }, $request->validated('skus'));

        return new self(
            name: $request->validated('name'),
            brand_id: (int) $request->validated('brand_id'),
            category_id: $request->validated('category_id'),
            description: $request->validated('description'),
            is_active: $request->boolean('is_active'),
            is_alcoholic: $request->boolean('is_alcoholic'),
            image: $request->file('image'),
            skus: $skus
        );
    }
}