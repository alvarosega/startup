<?php

namespace App\Actions\Admin\Sku;

use App\Models\{Product, Sku};
use App\DTOs\Admin\Sku\SkuDataDTO;
use Illuminate\Support\Facades\{DB, Storage};

class CreateSkuAction
{
    public function execute(string $productId, SkuDataDTO $data): Sku
    {
        return DB::transaction(function () use ($productId, $data) {
            // Verificamos existencia del padre con bloqueo
            Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $sku = Sku::create([
                'product_id'        => $productId,
                'name'              => $data->name,
                'code'              => $data->code, // Si es null, el modelo genera el EAN-13
                'base_price'        => $data->price,
                'conversion_factor' => $data->conversionFactor,
                'weight'            => $data->weight,
                'is_active'         => $data->isActive,
            ]);

            if ($data->image) {
                $sku->update(['image_path' => $data->image->store('skus', 'public')]);
            }

            return $sku;
        });
    }
}