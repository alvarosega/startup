<?php
namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use App\DTOs\Admin\Sku\SkuDataDTO;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpdateSkuAction
{
    public function execute(Sku $sku, SkuDataDTO $dto): void
    {
        DB::transaction(function () use ($sku, $dto) {
            $data = [
                'name'              => $dto->name,
                'code'              => $dto->code,
                'base_price'        => $dto->price,
                'conversion_factor' => $dto->conversionFactor,
                'weight'            => $dto->weight,
                'is_active'         => $dto->isActive,
            ];

            if ($dto->image) {
                if ($sku->image_path) {
                    Storage::disk('public')->delete($sku->image_path);
                }
                $data['image_path'] = $dto->image->store('skus', 'public');
            }

            $sku->update($data);
            
            // LA LEY: Siempre invalidar la caché al mutar datos estructurales
            Cache::forget('admin_products_list');
        });
    }
}