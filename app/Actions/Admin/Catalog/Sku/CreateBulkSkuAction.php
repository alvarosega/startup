<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Sku;

use App\Models\Catalog\Product;
use App\Models\Catalog\Sku;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class CreateBulkSkuAction
{
    /**
     * @param array<\App\DTOs\Admin\Catalog\Sku\SkuDataDTO> $skusData
     */
    public function execute(string $productId, array $skusData): void
    {
        if (count($skusData) > 50) {
            throw new InvalidArgumentException("EXCESO_DE_CARGA: Límite de seguridad de 50 SKUs por bloque excedido.");
        }

        DB::transaction(function () use ($productId, $skusData) {
            Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            // Optimización algorítmica: Leer el orden máximo una sola vez fuera del bucle
            $nextSortOrder = (int) Sku::where('product_id', $productId)
                ->where('deleted_epoch', 0)
                ->max('sort_order');

            foreach ($skusData as $data) {
                $nextSortOrder += 10;

                // Sincronización con las propiedades snake_case corregidas del DTO
                Sku::create([
                    'product_id'        => $productId,
                    'name'              => $data->name,
                    'code'              => $data->code, 
                    'base_price'        => $data->base_price,
                    'conversion_factor' => $data->conversion_factor,
                    'weight'            => $data->weight,
                    'is_active'         => $data->is_active,
                    'sort_order'        => $nextSortOrder,
                ]);
            }
        });
    }
}