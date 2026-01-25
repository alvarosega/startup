<?php

namespace App\Actions\Brand;

use App\DTOs\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateBrand
{
    public function execute(Brand $brand, BrandData $data): Brand
    {
        $attributes = $data->toArray();

        // 1. Slug
        if (empty($attributes['slug']) && $brand->name !== $attributes['name']) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        // 2. Imagen (Borrar anterior si hay nueva)
        if ($data->image) {
            if ($brand->image_path) {
                Storage::disk('public')->delete($brand->image_path);
            }
            $attributes['image_path'] = $data->image->store('brands', 'public');
        }

        // 3. Actualizar
        $brand->update($attributes);

        // 4. Relaciones
        $brand->categories()->sync($data->categories);

        return $brand->fresh();
    }
}