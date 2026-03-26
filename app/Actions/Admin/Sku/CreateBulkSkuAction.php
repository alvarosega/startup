<?php

namespace App\Actions\Admin\Sku;

use App\Models\{Product, Sku};
use App\DTOs\Admin\Sku\SkuDataDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBulkSkuAction
{
    /**
     * @param string $productId
     * @param SkuDataDTO[] $skusData
     */
    public function execute(string $productId, array $skusData): void
    {
        // LA LEY: Límite de 50 registros por transacción (Control de Hostinger)
        if (count($skusData) > 50) {
            throw new \InvalidArgumentException("EXCESO_DE_CARGA: Máximo 50 SKUs por bloque.");
        }

        DB::transaction(function () use ($productId, $skusData) {
            // Bloqueo del Maestro
            Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            foreach ($skusData as $data) {
                // El modelo Sku se encargará del EAN-13 si code es null (booted event)
                Sku::create([
                    'product_id'        => $productId,
                    'name'              => $data->name,
                    'code'              => $data->code,
                    'base_price'        => $data->price,
                    'conversion_factor' => $data->conversionFactor,
                    'weight'            => $data->weight,
                    'is_active'         => $data->isActive,
                ]);
            }
        });
    }
}