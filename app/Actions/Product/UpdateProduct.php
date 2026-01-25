<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductData;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateProduct
{
    public function execute(Product $product, ProductData $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $attributes = $data->toArray();

            // Slug solo si cambia nombre
            if ($product->name !== $attributes['name']) {
                $attributes['slug'] = Str::slug($attributes['name']);
            }

            // Imagen
            if ($data->image) {
                if ($product->image_path) Storage::disk('public')->delete($product->image_path);
                $attributes['image_path'] = $data->image->store('products', 'public');
            }

            $product->update($attributes);

            // --- SINCRONIZACIÓN DE SKUS ---
            
            // 1. Obtener IDs que vienen en el request (los que sobreviven)
            $incomingIds = array_filter(array_map(fn($s) => $s->id, $data->skus));
            
            // 2. Eliminar los que no vinieron (SoftDelete)
            $product->skus()->whereNotIn('id', $incomingIds)->delete();

            // 3. Upsert (Actualizar o Crear)
            foreach ($data->skus as $skuData) {
                if ($skuData->id) {
                    // Actualizar existente
                    $sku = Sku::find($skuData->id);
                    if ($sku) {
                        $sku->update($skuData->toArray());
                        $sku->updatePrice($skuData->price);
                    }
                } else {
                    // Crear nuevo
                    /** @var Sku $sku */
                    $sku = $product->skus()->create($skuData->toArray());
                    $sku->updatePrice($skuData->price);
                }
            }

            return $product->fresh(['skus.prices']);
        });
    }
}