<?php

declare(strict_types=1);

namespace App\Actions\Admin\Sku;

use App\Models\{Product, Sku};
use Illuminate\Support\Facades\DB;

class CreateBulkSkuAction
{
    /**
     * @param array<\App\DTOs\Admin\Sku\SkuDataDTO> $skusData
     */
    public function execute(string $productId, array $skusData): void
    {
        if (count($skusData) > 50) {
            throw new \InvalidArgumentException("EXCESO_DE_CARGA: Límite de seguridad de 50 SKUs por bloque excedido.");
        }

        DB::transaction(function () use ($productId, $skusData) {
            Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            foreach ($skusData as $data) {
                $maxSortOrder = Sku::where('product_id', $productId)
                    ->where('deleted_epoch', 0)
                    ->max('sort_order');

                Sku::create([
                    'product_id'        => $productId,
                    'name'              => $data->name,
                    'code'              => $data->code, 
                    'base_price'        => $data->price,
                    'conversion_factor' => $data->conversionFactor,
                    'weight'            => $data->weight,
                    'is_active'         => $data->isActive,
                    'sort_order'        => $maxSortOrder ? $maxSortOrder + 1 : 1,
                ]);
            }
        });
    }
}