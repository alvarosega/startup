<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Sku;

use App\Models\Catalog\Sku;
use App\DTOs\Admin\Catalog\Sku\SkuDataDTO;
use Illuminate\Support\Facades\{DB, Storage};

class UpdateSkuAction
{
    /**
     * RECTIFICACIÓN: Reestructuración transaccional atómica bajo la directiva afterCommit.
     */
    public function execute(Sku $sku, SkuDataDTO $data): void
    {
        $pathsToClean = [];
        $oldPath = null;

        DB::transaction(function () use ($sku, $data, &$pathsToClean, &$oldPath) {
            $lockedSku = Sku::where('id', $sku->id)->lockForUpdate()->firstOrFail();
            $oldPath = $lockedSku->image_path;

            $attributes = [
                'name'              => $data->name,
                'base_price'        => $data->base_price,
                'conversion_factor' => $data->conversion_factor,
                'weight'            => $data->weight,
                'is_active'         => $data->is_active,
            ];

            if ($data->image) {
                $attributes['image_path'] = $data->image->store('skus', 'public');
                $pathsToClean[] = $attributes['image_path'];
            }

            $lockedSku->update($attributes);

            DB::afterCommit(function () use ($data, $oldPath) {
                if ($data->image && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            });
        });
    }
}