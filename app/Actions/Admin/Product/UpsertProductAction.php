<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use App\DTOs\Admin\Product\ProductData;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertProductAction
{
    public function execute(ProductData $data, ?Product $product = null): Product
    {
        return DB::transaction(function () use ($data, $product) {
            $isNew = !$product;
            $oldPath = $product?->image_path;

            $attributes = [
                'brand_id'     => $data->brandId,
                'category_id'  => $data->categoryId,
                'name'         => $data->name,
                // Solo generamos slug si es nuevo. Evitamos cambiar URIs existentes accidentalmente.
                'slug'         => $isNew ? $data->slug : $product->slug,
                'description'  => $data->description,
                'is_active'    => $data->isActive,
                'is_alcoholic' => $data->isAlcoholic,
            ];

            if ($data->image) {
                $attributes['image_path'] = $data->image->store('products', 'public');
            }

            if ($isNew) {
                $product = Product::create($attributes);
            } else {
                $product->update($attributes);
                if ($data->image && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // DoD v2.0: Invalidación atómica
            Cache::forget('admin_products_list');

            return $product;
        });
    }
}