<?php

namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use App\DTOs\Admin\Sku\CreateBulkSkuDTO;
use Illuminate\Support\Facades\DB;

class CreateBulkSkuAction
{
    public function execute(string $productId, CreateBulkSkuDTO $dto): void
    {
        DB::transaction(function () use ($productId, $dto) {
            foreach ($dto->skus as $skuData) {
                $sku = Sku::create([
                    'product_id'        => $productId,
                    'name'              => $skuData->name,
                    'code'              => $skuData->code,
                    'base_price'        => $skuData->price,
                    'conversion_factor' => $skuData->conversionFactor,
                    'weight'            => $skuData->weight,
                    'image_path'        => $skuData->image ? $skuData->image->store('skus', 'public') : null
                ]);

                // Generación de precio inicial
                $sku->prices()->create([
                    'type'        => 'regular',
                    'list_price'  => $skuData->price,
                    'final_price' => $skuData->price,
                    'valid_from'  => now()
                ]);
            }
        });
    }
}
