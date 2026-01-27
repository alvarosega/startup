<?php

namespace App\Actions\Bundle;

use App\DTOs\Bundle\BundleDTO;
use App\Models\Bundle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpsertBundleAction
{
    public function execute(BundleDTO $dto, ?Bundle $bundle = null): Bundle
    {
        return DB::transaction(function () use ($dto, $bundle) {
            
            // 1. Manejo de Imagen
            $imagePath = $bundle?->image_path;
            
            if ($dto->image) {
                if ($bundle?->image_path) {
                    Storage::disk('public')->delete($bundle->image_path);
                }
                $imagePath = $dto->image->store('bundles', 'public');
            }

            // 2. Datos base
            $data = [
                'branch_id' => $dto->branchId,
                'name' => $dto->name,
                'description' => $dto->description,
                'fixed_price' => $dto->fixedPrice,
                'is_active' => $dto->isActive,
                'image_path' => $imagePath,
            ];

            // 3. Crear o Actualizar
            if (!$bundle) {
                // Generar slug único
                $data['slug'] = Str::slug($dto->name) . '-' . Str::random(4);
                $bundle = Bundle::create($data);
            } else {
                $bundle->update($data);
            }

            // 4. Sincronizar Items (SKUs)
            // Transformamos el array para sync: [id => ['quantity' => x]]
            $syncData = [];
            foreach ($dto->items as $item) {
                if (!empty($item['sku_id'])) {
                    $syncData[$item['sku_id']] = ['quantity' => $item['quantity']];
                }
            }
            
            $bundle->skus()->sync($syncData);

            return $bundle;
        });
    }
}