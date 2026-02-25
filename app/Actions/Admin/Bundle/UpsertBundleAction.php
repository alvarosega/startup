<?php

namespace App\Actions\Admin\Bundle;

use App\DTOs\Admin\Bundle\BundleDTO;
use App\Models\Bundle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertBundleAction
{
    public function execute(BundleDTO $dto, ?Bundle $bundle = null): Bundle
    {
        // Regla Senior: La transacción DEBE ser devuelta (return DB::transaction...)
        return DB::transaction(function () use ($dto, $bundle) {
            $attributes = [
                'branch_id'   => $dto->branchId,
                'name'        => $dto->name,
                'description' => $dto->description,
                'fixed_price' => $dto->fixedPrice,
                'is_active'   => $dto->isActive,
            ];

            if (!$bundle) {
                $attributes['slug'] = Str::slug($dto->name) . '-' . Str::random(4);
            }

            // Manejo de Imagen
            $oldPath = $bundle?->image_path;
            $newPath = null;

            if ($dto->image) {
                $newPath = $dto->image->store('bundles', 'public');
                $attributes['image_path'] = $newPath;
            }

            // Guardado
            if (!$bundle) {
                $bundle = Bundle::create($attributes);
            } else {
                $bundle->update($attributes);
            }

            // Sincronización de Items
            $syncData = [];
            foreach ($dto->items as $item) {
                if (!empty($item['sku_id'])) {
                    $syncData[$item['sku_id']] = ['quantity' => $item['quantity']];
                }
            }
            $bundle->skus()->sync($syncData);

            // Protocolo No-Redis (Limpieza Estándar)
            try {
                Cache::forget('catalog_home_data');
                Cache::forget('bundles_list_all');
                
                // Si hubo cambio de imagen, borrar la anterior
                if ($newPath && $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            } catch (\Exception $e) {
                report($e);
            }

            // CRÍTICO: La función anónima DEBE devolver el objeto
            return $bundle;
        });
    }
}