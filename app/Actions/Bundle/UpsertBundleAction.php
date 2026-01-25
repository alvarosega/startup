<?php

namespace App\Actions\Bundle;

use App\Models\Bundle;
use App\DTOs\Bundle\BundleDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpsertBundleAction
{
    public function execute(BundleDTO $dto, ?Bundle $bundle = null): Bundle
    {
        return DB::transaction(function () use ($dto, $bundle) {
            $data = [
                'name' => $dto->name,
                'description' => $dto->description,
                'fixed_price' => $dto->fixed_price,
                'is_active' => $dto->is_active,
            ];

            // Lógica de imagen: Solo procesamos si el DTO trae un archivo
            // Nota: En tu DTO debes asegurar que 'image' se mapee correctamente
            if (request()->hasFile('image')) {
                // 1. Eliminar la imagen vieja si existe
                if ($bundle && $bundle->image_path) {
                    Storage::disk('public')->delete($bundle->image_path);
                }
                
                // 2. Guardar la nueva
                $data['image_path'] = request()->file('image')->store('bundles', 'public');
            }

            if (!$bundle) {
                $data['slug'] = Str::slug($dto->name) . '-' . Str::lower(Str::random(6));
                $bundle = Bundle::create($data);
            } else {
                $bundle->update($data);
            }

            // Sincronizar items
            $bundle->skus()->sync($dto->items);

            return $bundle;
        });
    }
}
