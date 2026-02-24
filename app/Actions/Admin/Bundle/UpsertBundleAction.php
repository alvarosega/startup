<?php

namespace App\Actions\Admin\Bundle;

use App\DTOs\Admin\Bundle\BundleDTO; // Namespace corregido
use App\Models\Bundle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertBundleAction
{
    public function execute(BundleDTO $dto, ?Bundle $bundle = null): Bundle
    {
        return DB::transaction(function () use ($dto, $bundle) {
            $attributes = [
                'branch_id'   => $dto->branchId,
                'name'        => $dto->name,
                'description' => $dto->description,
                'fixed_price' => $dto->fixedPrice,
                'is_active'   => $dto->isActive,
            ];

            // Generar slug si es nuevo
            if (!$bundle) {
                $attributes['slug'] = Str::slug($dto->name) . '-' . Str::random(4);
            }

            // Manejo Atómico de Imagen
            $oldPath = $bundle?->image_path;
            $newPath = null;

            if ($dto->image) {
                $newPath = $dto->image->store('bundles', 'public');
                $attributes['image_path'] = $newPath;
            }

            try {
                if (!$bundle) {
                    $bundle = Bundle::create($attributes);
                } else {
                    $bundle->update($attributes);
                }

                // Sincronizar Items
                $syncData = [];
                foreach ($dto->items as $item) {
                    if (!empty($item['sku_id'])) {
                        $syncData[$item['sku_id']] = ['quantity' => $item['quantity']];
                    }
                }
                $bundle->skus()->sync($syncData);

                // Éxito: Limpiar imagen vieja si hubo reemplazo
                if ($newPath && $oldPath) Storage::disk('public')->delete($oldPath);

                // RENDIMIENTO: Invalidar caché de bundles
                Cache::tags(['catalog', 'bundles'])->flush();

                return $bundle;

            } catch (\Exception $e) {
                // Fallo: Borrar la imagen nueva que no se registró
                if ($newPath) Storage::disk('public')->delete($newPath);
                throw $e;
            }
        });
    }
}