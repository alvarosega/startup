<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;

class GetBrandForEditAction
{
    /**
     * RECTIFICACIÓN: Se aisla la transformación estructural del modelo a tipos planos nativos,
     * eliminando la contaminación de lógica de datos dentro de las respuestas del controlador.
     */
    public function execute(Brand $brand): array
    {
        return [
            'id'          => (string) $brand->id,
            'parent_id'   => $brand->parent_id ? (string) $brand->parent_id : null,
            'provider_id' => (string) $brand->provider_id,
            'category_id' => (string) $brand->category_id,
            'name'        => (string) $brand->name,
            'slug'        => (string) $brand->slug,
            'bg_color'    => $brand->bg_color ? (string) $brand->bg_color : null,
            'image_path'  => $brand->image_path ? (string) $brand->image_path : null,
            'website'     => $brand->website ? (string) $brand->website : null,
            'is_active'   => (bool) $brand->is_active,
            'is_featured' => (bool) $brand->is_featured,
            'description' => $brand->description ? (string) $brand->description : null,
        ];
    }
}