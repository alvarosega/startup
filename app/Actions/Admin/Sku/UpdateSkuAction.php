<?php

namespace App\Actions\Admin\Sku;

use App\Models\Sku;
use App\DTOs\Admin\Sku\SkuDataDTO;
use Illuminate\Support\Facades\{DB, Storage};

class UpdateSkuAction
{
    public function execute(Sku $sku, SkuDataDTO $data): void
    {
        DB::transaction(function () use ($sku, $data) {
            // LA LEY: Bloqueo de fila antes de mutar
            $lockedSku = Sku::where('id', $sku->id)->lockForUpdate()->firstOrFail();

            $attributes = [
                'name'              => $data->name,
                'code'              => $data->code,
                'base_price'        => $data->price,
                'conversion_factor' => $data->conversionFactor,
                'weight'            => $data->weight,
                'is_active'         => $data->isActive,
            ];

            if ($data->image) {
                if ($lockedSku->image_path) {
                    Storage::disk('public')->delete($lockedSku->image_path);
                }
                $attributes['image_path'] = $data->image->store('skus', 'public');
            }

            $lockedSku->update($attributes);
        });
    }
}
