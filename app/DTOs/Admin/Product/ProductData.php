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
        // NOTA: Eliminado el array de SKUs. La gestión de SKUs debe tener su propio
        // endpoint/Action para mantener la atomicidad. "1 Action = 1 Caso de Uso"
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            // Generación de slug segura para evitar colisiones iniciales
            slug: Str::slug($request->validated('name')) . '-' . Str::random(4),
            brandId: $request->validated('brand_id'),
            categoryId: $request->validated('category_id'),
            description: $request->validated('description'),
            isActive: $request->boolean('is_active', true),
            isAlcoholic: $request->boolean('is_alcoholic', false),
            image: $request->file('image')
        );
    }
}