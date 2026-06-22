<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;
use App\DTOs\Admin\Catalog\Product\ProductData;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertProductAction
{
    public function execute(ProductData $data, ?Product $product = null): Product
    {
        $isUpdate = !is_null($product);
        $pathsToClean = [];
        $oldPath = null;

        DB::beginTransaction();
        try {
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
                    throw ValidationException::withMessages([
                        'name' => 'CONFLICTO_INTEGRIDAD: El slug automático derivado ya se encuentra registrado.'
                    ]);
                }
                
                $maxSortOrder = Product::where('deleted_epoch', 0)->max('sort_order');
                $product->sort_order = $maxSortOrder ? $maxSortOrder + 1 : 1;
            }

            if ($data->image) {
                $product->image_path = $data->image->store('products', 'public');
                $pathsToClean[] = $product->image_path;
            }

            // Sincronización estricta con las propiedades snake_case del DTO unificado
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
            DB::commit();

            // Remoción física diferida únicamente tras confirmación del commit
            if ($data->image && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            
            foreach ($pathsToClean as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}