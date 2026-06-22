<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Catalog\Product;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class ProductData
{
    public function __construct(
        public string $name,
        public string $brand_id,
        public string $category_id,
        public ?string $description,
        public bool $is_active,
        public bool $is_alcoholic,
        public string $idempotency_key,
        public ?UploadedFile $image
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            brand_id: (string) $validated['brand_id'],
            category_id: (string) $validated['category_id'],
            description: $validated['description'] ?? null,
            is_active: $request->boolean('is_active'),
            is_alcoholic: $request->boolean('is_alcoholic'),
            idempotency_key: (string) ($validated['idempotency_key'] ?? $request->header('X-Idempotency-Key')),
            image: $request->file('image')
        );
    }
}