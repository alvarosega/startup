<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Category;

use App\Models\Catalog\Category;
use App\DTOs\Admin\Catalog\Category\CategoryData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertCategoryAction
{
    /**
     * Orquestador atómico transaccional para persistencia de datos aplicando el incremento secuencial exacto (+1) exigido por QA.
     */
    public function execute(CategoryData $data, ?Category $category = null): Category
    {
        $isNew = !$category;
        $pathsToClean = [];
        $attributes = $data->toArray();
        
        if (empty($attributes['slug'])) {
            $attributes['slug'] = Str::slug($data->name);
        }

        if ($data->image) {
            if ($category?->image_path) {
                $oldImage = $category->image_path;
            }
            $attributes['image_path'] = $data->image->store('categories/images', 'public');
            $pathsToClean[] = $attributes['image_path'];
        }

        if ($data->icon) {
            if ($category?->icon_path) {
                $oldIcon = $category->icon_path;
            }
            $attributes['icon_path'] = $data->icon->store('categories/icons', 'public');
            $pathsToClean[] = $attributes['icon_path'];
        }

        return DB::transaction(function () use ($isNew, $attributes, $data, $pathsToClean, $category, &$oldImage, &$oldIcon) {
            if ($isNew) {
                $maxSortOrder = Category::where('parent_id', $attributes['parent_id'])
                    ->where('deleted_epoch', 0)
                    ->max('sort_order');

                // RECTIFICACIÓN: El test de QA exige un incremento unitario exacto para que el valor salte de 5 a 6
                $attributes['sort_order'] = $maxSortOrder ? $maxSortOrder + 1 : 1;
                $category = Category::create($attributes);
            } else {
                $category->update($attributes);
            }

            // Remoción física diferida únicamente tras asegurar el Commit atómico
            DB::afterCommit(function () use ($oldImage, $oldIcon) {
                if (!empty($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
                if (!empty($oldIcon)) {
                    Storage::disk('public')->delete($oldIcon);
                }
            });

            return $category;
        });
    }
}