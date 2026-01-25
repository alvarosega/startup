<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\DTOs\Product\ProductDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException; // Importante

class UpsertProductAction
{
    public function execute(ProductDTO $dto, ?Product $product = null): Product
    {
        return DB::transaction(function () use ($dto, $product) {
            $data = [
                'name' => $dto->name,
                'brand_id' => $dto->brand_id,
                'category_id' => $dto->category_id,
                'description' => $dto->description,
                'is_active' => $dto->is_active,
                'is_alcoholic' => $dto->is_alcoholic,
            ];

            // Imagen Maestra
            if ($dto->image instanceof UploadedFile) {
                if ($product && $product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $dto->image->store('products', 'public');
            }

            if (!$product) {
                $data['slug'] = Str::slug($dto->name) . '-' . Str::random(5);
                $product = Product::create($data);
            } else {
                $product->update($data);
            }

            // Aquí es donde ocurre el error. Lo envolvemos en try-catch.
            try {
                $this->syncSkus($product, $dto->skus);
            } catch (\Illuminate\Database\QueryException $e) {
                // Código de error MySQL para DUPLICATE ENTRY es 1062
                if ($e->errorInfo[1] == 1062) {
                    throw ValidationException::withMessages([
                        'skus' => 'Error Crítico: Uno de los códigos EAN ya existe en el sistema (posiblemente en papelera). Por favor, verifica los códigos.',
                    ]);
                }
                throw $e; // Re-lanzar si no es duplicado
            }

            return $product;
        });
    }

    private function syncSkus(Product $product, array $skuDtos): void
    {
        $currentIds = [];

        foreach ($skuDtos as $skuDto) {
            $skuData = [
                'name' => $skuDto->name,
                'code' => $skuDto->code,
                'conversion_factor' => $skuDto->conversion_factor,
                'weight' => $skuDto->weight,
                'is_active' => true,
            ];

            // Imagen por SKU
            if ($skuDto->image instanceof UploadedFile) {
                $skuData['image_path'] = $skuDto->image->store('skus', 'public');
            }

            $sku = $product->skus()->updateOrCreate(
                ['id' => $skuDto->id],
                $skuData
            );

            // Precio
            $currentPrice = $sku->prices()->whereNull('branch_id')->latest()->first();
            if (!$currentPrice || (float)$currentPrice->final_price !== $skuDto->price) {
                if ($currentPrice) $currentPrice->delete();
                
                $sku->prices()->create([
                    'branch_id' => null,
                    'list_price' => $skuDto->price * 1.10, 
                    'final_price' => $skuDto->price,
                    'min_quantity' => 1,
                    'valid_from' => now(),
                ]);
            }
            
            $currentIds[] = $sku->id;
        }

        if ($product->exists) {
            $product->skus()->whereNotIn('id', $currentIds)->delete();
        }
    }
}