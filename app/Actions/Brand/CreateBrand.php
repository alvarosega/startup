<?php

namespace App\Actions\Brand;

use App\DTOs\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Support\Str;

class CreateBrand
{
    public function execute(BrandData $data): Brand
    {
        $attributes = $data->toArray();

        // 1. Generar Slug si no viene
        if (empty($attributes['slug'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        // 2. Manejo de Imagen
        if ($data->image) {
            $attributes['image_path'] = $data->image->store('brands', 'public');
        }

        // 3. Crear
        $brand = Brand::create($attributes);

        // 4. Relaciones
        if (!empty($data->categories)) {
            $brand->categories()->sync($data->categories);
        }

        return $brand;
    }
}