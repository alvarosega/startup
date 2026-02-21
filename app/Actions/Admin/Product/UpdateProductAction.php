<?php
namespace App\Actions\Admin\Product;

use App\Models\Product;
use App\DTOs\Admin\Product\UpdateProductDTO;
use Illuminate\Support\Facades\Storage;

class UpdateProductAction
{
    public function execute(Product $product, UpdateProductDTO $dto): void
    {
        $data = [
            'name'         => $dto->name,
            'brand_id'     => $dto->brandId,
            'category_id'  => $dto->categoryId,
            'description'  => $dto->description,
            'is_active'    => $dto->isActive,
            'is_alcoholic' => $dto->isAlcoholic,
        ];

        if ($dto->image) {
            if ($product->image_path) Storage::disk('public')->delete($product->image_path);
            $data['image_path'] = $dto->image->store('products', 'public');
        }

        $product->update($data);
    }
}