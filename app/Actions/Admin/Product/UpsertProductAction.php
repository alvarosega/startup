<?php

declare(strict_types=1);

namespace App\Actions\Admin\Product;

use App\Models\Product;
use App\DTOs\Admin\Product\ProductData;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertProductAction
{
    public function execute(ProductData $data, ?Product $product = null): Product
    {
        return DB::transaction(function () use ($data, $product) {
            $isUpdate = !is_null($product);
            
            if ($isUpdate) {
                $product = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();
            } else {
                $product = new Product();
            }

            $newSlug = $isUpdate ? $product->slug : Str::slug($data->name);
            
            if (!$isUpdate) {
                $slugExists = Product::where('slug', $newSlug)->where('deleted_epoch', 0)->exists();
                if ($slugExists) {
                    throw ValidationException::withMessages([
                        'name' => 'CONFLICTO_INTEGRIDAD: El slug automático derivado ya se encuentra registrado.'
                    ]);
                }
                
                // Algoritmo de Ordenamiento Lineal (+1)
                $maxSortOrder = Product::where('deleted_epoch', 0)->max('sort_order');
                $product->sort_order = $maxSortOrder ? $maxSortOrder + 1 : 1;
            }

            if ($data->image) {
                if ($isUpdate && $product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product->image_path = $data->image->store('products', 'public');
            }

            $product->fill([
                'name'         => $data->name,
                'slug'         => $newSlug,
                'brand_id'     => $data->brandId,
                'category_id'  => $data->categoryId,
                'description'  => $data->description,
                'is_active'    => $data->isActive,
                'is_alcoholic' => $data->isAlcoholic,
            ]);

            $product->save();

            return $product;
        });
    }
}