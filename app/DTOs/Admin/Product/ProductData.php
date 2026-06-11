<?php

declare(strict_types=1);

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
        public ?string $idempotencyKey,
        public ?UploadedFile $image
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->validated('name'),
            brandId: (string) $request->validated('brand_id'),
            categoryId: (string) $request->validated('category_id'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active'),
            isAlcoholic: (bool) $request->validated('is_alcoholic'),
            idempotencyKey: $request->validated('idempotencyKey'),
            image: $request->file('image')
        );
    }
}