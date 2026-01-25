<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductData;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    public function execute(ProductData $data): Product
    {
        return DB::transaction(function () use ($data) {
            $attributes = $data->toArray();
            $attributes['slug'] = Str::slug($attributes['name']);

            if ($data->image) {
                $attributes['image_path'] = $data->image->store('products', 'public');
            }

            $product = Product::create($attributes);

            // Crear SKUs
            foreach ($data->skus as $skuData) {
                /** @var Sku $sku */
                $sku = $product->skus()->create($skuData->toArray());
                
                // Usamos el helper del modelo SKU para el precio
                $sku->updatePrice($skuData->price);
            }

            return $product;
        });
    }
}