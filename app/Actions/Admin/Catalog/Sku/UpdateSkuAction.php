<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Sku;

use App\Models\Catalog\Sku;
use App\DTOs\Admin\Catalog\Sku\SkuDataDTO;
use Illuminate\Support\Facades\{DB, Storage};

class UpdateSkuAction
{
    public function execute(Sku $sku, SkuDataDTO $data): void
    {
        $pathsToClean = [];
        $oldPath = null;

        DB::beginTransaction();
        try {
            $lockedSku = Sku::where('id', $sku->id)->lockForUpdate()->firstOrFail();
            $oldPath = $lockedSku->image_path;

            // Sincronización rigurosa con nomenclatura snake_case del DTO e inmutabilidad del código controlada por el Modelo
            $attributes = [
                'name'              => $data->name,
                'code'              => $data->code,
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
            DB::commit();

            // Aislamiento de efectos secundarios físicos post-commit para prevenir pérdida de archivos ante fallos SQL
            if ($data->image && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            foreach ($pathsToClean as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}