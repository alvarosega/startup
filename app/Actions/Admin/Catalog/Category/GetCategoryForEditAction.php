<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Category;

use App\Models\Catalog\Category;

class GetCategoryForEditAction
{
    /**
     * Extrae, limpia y estructura los datos planos de la categoría y sus dependencias jerárquicas
     * para la correcta rehidratación del formulario en Vue sin recurrir a JsonResources.
     */
    public function execute(Category $category): array
    {
        $mappedCategory = [
            'id'                 => (string) $category->id,
            'parent_id'          => $category->parent_id ? (string) $category->parent_id : null,
            'name'               => (string) $category->name,
            'slug'               => (string) $category->slug,
            'external_code'      => $category->external_code ? (string) $category->external_code : null,
            'tax_classification' => $category->tax_classification ? (string) $category->tax_classification : null,
            'requires_age_check' => (bool) $category->requires_age_check,
            'is_active'          => (bool) $category->is_active,
            'is_featured'        => (bool) $category->is_featured,
            'bg_color'           => $category->bg_color ? (string) $category->bg_color : null,
            'description'        => $category->description ? (string) $category->description : null,
            'seo_title'          => $category->seo_title ? (string) $category->seo_title : null,
            'seo_description'    => $category->seo_description ? (string) $category->seo_description : null,
            'image_path'         => $category->image_path ? (string) $category->image_path : null,
            'icon_path'          => $category->icon_path ? (string) $category->icon_path : null,
        ];

        // Obtener posibles padres excluyendo a la categoría actual para prevenir bucles en el frontend
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => ['id' => (string) $p->id, 'name' => (string) $p->name])
            ->toArray();

        return [
            'category' => $mappedCategory,
            'parents'  => $parents
        ];
    }
}