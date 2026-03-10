<?php
namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use App\DTOs\Admin\Sku\CreateBulkSkuDTO;
use Illuminate\Support\Facades\{DB, Cache};

class CreateBulkSkuAction
{
    public function execute(string $productId, CreateBulkSkuDTO $dto): void
    {
        DB::transaction(function () use ($productId, $dto) {
            foreach ($dto->skus as $skuData) {
                Sku::create([
                    'product_id'        => $productId,
                    'name'              => $skuData->name,
                    'code'              => $skuData->code,
                    'base_price'        => $skuData->price, // Aquí sí es base_price porque el DTO lo mapeó así
                    'conversion_factor' => $skuData->conversionFactor,
                    'weight'            => $skuData->weight,
                    'image_path'        => $skuData->image ? $skuData->image->store('skus', 'public') : null,
                    'is_active'         => true,
                ]);

                // LA LEY: ELIMINADO el bloque de $sku->prices()->create(...)
                // Los precios financieros reales (list/final) se gestionan 
                // en el módulo PriceController. El SKU solo guarda su base_price referencial.
            }
            
            // Invalida la caché del catálogo para que Vue lo detecte de inmediato
            Cache::forget('admin_products_list');
        });
    }
}