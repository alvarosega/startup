<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;
use App\DTOs\Admin\Product\ProductData;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertProductAction
{
    public function execute(ProductData $data, ?string $productId = null): Product
    {
        $cacheKey = $data->idempotencyKey ? "idemp_prod_{$data->idempotencyKey}" : null;

        // 1. BLINDAJE DE IDEMPOTENCIA
        if ($cacheKey && Cache::has($cacheKey)) {
            return Product::findOrFail(Cache::get($cacheKey));
        }

        return DB::transaction(function () use ($data, $productId, $cacheKey) {
            $isUpdate = !is_null($productId);
            
            // 2. BLOQUEO PESIMISTA
            $product = $isUpdate 
                ? Product::where('id', $productId)->lockForUpdate()->firstOrFail() 
                : new Product();

            // 3. INTEGRIDAD DE SLUG (Inmutable en Update si ya existe)
            // LEY: No permitimos cambios de slug post-creación para preservar trazabilidad
            $newSlug = $isUpdate ? $product->slug : Str::slug($data->name);
            
            if (!$isUpdate) {
                $slugExists = Product::where('slug', $newSlug)->exists();
                if ($slugExists) {
                    throw ValidationException::withMessages(['name' => 'CONFLICTO_INTEGRIDAD: El slug generado ya existe.']);
                }
            }

            // 4. GESTIÓN FÍSICA
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

            if ($cacheKey) {
                Cache::put($cacheKey, $product->id, 86400);
            }

            return $product;
        });
    }
}