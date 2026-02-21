<?php
namespace App\DTOs\Admin\Product;

use Illuminate\Http\UploadedFile;

readonly class CreateProductDTO
{
    public function __construct(
        public string $name,
        public string $brandId,
        public string $categoryId,
        public ?string $description,
        public bool $isActive,
        public bool $isAlcoholic,
        public ?UploadedFile $image
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            brandId: $request->validated('brand_id'),
            categoryId: $request->validated('category_id'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', true),
            isAlcoholic: (bool) $request->validated('is_alcoholic', false),
            image: $request->file('image')
        );
    }
}