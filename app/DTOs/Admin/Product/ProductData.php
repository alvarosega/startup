<?php

namespace App\DTOs\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

readonly class ProductData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $brandId,
        public string $categoryId,
        public ?string $description,
        public bool $isActive,
        public bool $isAlcoholic,
        public ?UploadedFile $image
    ) {}

    public static function fromRequest(Request $request): self
    {
        // LA LEY: Generador de Slug con soporte UTF-8 (ñ y acentos)
        $name = $request->validated('name');
        $slugBase = mb_strtolower(trim($name));
        $slugBase = str_replace([' ', 'ñ'], ['-', 'ñ'], $slugBase);
        $slugBase = preg_replace('/[^a-z0-9ñ\-]/u', '', $slugBase);
        $finalSlug = $slugBase . '-' . Str::random(4);

        return new self(
            name:        $name,
            slug:        $finalSlug,
            brandId:     $request->validated('brand_id'),
            categoryId:  $request->validated('category_id'),
            description: $request->validated('description'),
            isActive:    $request->boolean('is_active', true),
            isAlcoholic: $request->boolean('is_alcoholic', false),
            image:       $request->file('image')
        );
    }
}