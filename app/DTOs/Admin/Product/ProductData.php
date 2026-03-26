<?php

namespace App\DTOs\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class ProductData
{
    public function __construct(
        public string $name,
        public string $brandId,
        public string $categoryId,
        public ?string $description,
        public bool $isActive,
        public bool $isAlcoholic,
        public ?UploadedFile $image,
        public ?string $idempotencyKey = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            brandId: (string) $request->validated('brand_id'),
            categoryId: (string) $request->validated('category_id'),
            description: $request->validated('description'),
            isActive: (bool) $request->boolean('is_active', true),
            isAlcoholic: (bool) $request->boolean('is_alcoholic', false),
            image: $request->file('image'),
            idempotencyKey: $request->header('X-Idempotency-Key')
        );
    }
}