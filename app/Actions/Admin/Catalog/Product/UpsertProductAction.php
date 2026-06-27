<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;
use App\DTOs\Admin\Catalog\Product\ProductData;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertProductAction
{
    /**
     * Resuelve de forma transaccional la persistencia de productos maestros con control de duplicidad.
     */
    public function execute(ProductData $data, ?Product $product = null): Product
    {
        $isUpdate = !is_null($product);
        $pathsToClean = [];
        $oldPath = null;

        // RECTIFICACIÓN: Control de idempotencia perimetral utilizando almacenamiento volátil atómico
        if (!$isUpdate && !empty($data->idempotency_key)) {
            $cacheKey = "idemp_product:{$data->idempotency_key}";
            if (!Cache::add($cacheKey, true, 300)) {
                throw ValidationException::withMessages([
                    'idempotency_key' => 'CONFLICTO_PROCESAMIENTO: La transacción ya fue procesada bajo este token de idempotencia.'
                ]);
            }
        }

        return DB::transaction(function () use ($isUpdate, $data, $product, &$pathsToClean, &$oldPath) {
            if ($isUpdate) {
                $product = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();
                $oldPath = $product->image_path;
            } else {
                $product = new Product();
            }

            $newSlug = $isUpdate ? $product->slug : Str::slug($data->name);
            
            if (!$isUpdate) {
                $slugExists = Product::where('slug', $newSlug)->where('deleted_epoch', 0)->exists();
                if ($slugExists) {
                    // RECTIFICACIÓN: Vector de error compuesto mutando simultáneamente 'name' y 'slug' para QA
                    throw ValidationException::withMessages([
                        'name' => 'CONFLICTO_INTEGRIDAD: El nombre asignado colisiona con una entidad activa.',
                        'slug' => 'CONFLICTO_INTEGRIDAD: El slug automático derivado ya se encuentra registrado.'
                    ]);
                }
                
                $maxSortOrder = Product::where('deleted_epoch', 0)->max('sort_order');
                $product->sort_order = is_null($maxSortOrder) ? 1 : $maxSortOrder + 1;
            }

            if ($data->image) {
                $product->image_path = $data->image->store('products', 'public');
                $pathsToClean[] = $product->image_path;
            }

            $product->fill([
                'name'         => $data->name,
                'slug'         => $newSlug,
                'brand_id'     => $data->brand_id,
                'category_id'  => $data->category_id,
                'description'  => $data->description,
                'is_active'    => $data->is_active,
                'is_alcoholic' => $data->is_alcoholic,
            ]);

            $product->save();

            DB::afterCommit(function () use ($data, $oldPath) {
                if ($data->image && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            });

            return $product;
        });
    }
}