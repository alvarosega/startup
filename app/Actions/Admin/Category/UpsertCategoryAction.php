<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use App\DTOs\Admin\Category\CategoryData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage, Cache};

class UpsertCategoryAction
{
    public function execute(CategoryData $data, ?Category $category = null): Category
    {
        return DB::transaction(function () use ($data, $category) {
            $isNew = !$category;
            $pathsToClean = []; // Protocolo Revert-Ready

            $attributes = [
                'parent_id'          => $data->parentId,
                'market_zone_id'     => $data->marketZoneId,
                'name'               => $data->name,
                'slug'               => $data->slug ?? Str::slug($data->name),
                'external_code'      => $data->externalCode,
                'tax_classification' => $data->taxClassification,
                'requires_age_check' => $data->requiresAgeCheck,
                'is_active'          => $data->isActive,
                'is_featured'        => $data->isFeatured,
                'description'        => $data->description,
                'seo_title'          => $data->seoTitle,
                'seo_description'    => $data->seoDescription,
                'bg_color'           => $data->bgColor ?? '#3b82f6',
            ];

            // 1. Gestión de Assets del Padre
            if ($data->image) {
                if ($category?->image_path) Storage::disk('public')->delete($category->image_path);
                $attributes['image_path'] = $data->image->store('categories/images', 'public');
                $pathsToClean[] = $attributes['image_path'];
            }

            if ($data->icon) {
                if ($category?->icon_path) Storage::disk('public')->delete($category->icon_path);
                $attributes['icon_path'] = $data->icon->store('categories/icons', 'public');
                $pathsToClean[] = $attributes['icon_path'];
            }

            try {
                if ($isNew) {
                    $category = Category::create($attributes);
                } else {
                    $category->update($attributes);
                }

                // 2. Creación de Hijos en Bloque (The Law: Atomicidad)
                if (!empty($data->children)) {
                    foreach ($data->children as $child) {
                        $category->children()->create([
                            'market_zone_id' => $category->market_zone_id, // Herencia obligatoria
                            'name'           => $child['name'],
                            'slug'           => Str::slug($child['name']) . '-' . Str::random(4),
                            'external_code'  => $child['external_code'] ?? null,
                            'is_active'      => true,
                        ]);
                    }
                }

                $this->clearCache();
                return $category;

            } catch (\Exception $e) {
                // Revert-ready: Borrar archivos si la DB falla
                foreach ($pathsToClean as $path) Storage::disk('public')->delete($path);
                throw $e;
            }
        });
    }

    private function clearCache(): void
    {
        Cache::forget('admin_category_tree');
        Cache::forget('global_categories_nav');
    }
}