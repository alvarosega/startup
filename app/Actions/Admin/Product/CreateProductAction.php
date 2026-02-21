<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use App\DTOs\Admin\Product\CreateProductDTO;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function execute(CreateProductDTO $dto): Product
    {
        return Product::create([
            'name' => $dto->name,
            'slug' => Str::slug($dto->name) . '-' . Str::random(4), // Evita colisiones de slug
            'brand_id' => $dto->brandId,
            'category_id' => $dto->categoryId,
            'description' => $dto->description,
            'is_active' => $dto->isActive,
            'is_alcoholic' => $dto->isAlcoholic,
            'image_path' => $dto->image ? $dto->image->store('products', 'public') : null
        ]);
    }
}