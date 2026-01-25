<?php

namespace App\DTOs\Product;

use Illuminate\Http\UploadedFile;
use App\DTOs\Sku\SkuData;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly int $brandId,
        public readonly string $categoryId,
        public readonly ?string $description,
        public readonly bool $isActive,
        public readonly bool $isAlcoholic,
        public readonly ?UploadedFile $image,
        /** @var SkuData[] */
        public readonly array $skus
    ) {}

    public static function fromRequest($request): self
    {
        $skus = array_map(
            fn($sku) => SkuData::fromArray($sku), 
            $request->validated('skus', [])
        );

        return new self(
            name: $request->validated('name'),
            brandId: (int) $request->validated('brand_id'),
            categoryId: $request->validated('category_id'),
            description: $request->validated('description'),
            isActive: $request->boolean('is_active', true),
            isAlcoholic: $request->boolean('is_alcoholic', false),
            image: $request->file('image'),
            skus: $skus
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'brand_id' => $this->brandId,
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'is_alcoholic' => $this->isAlcoholic,
        ];
    }
}