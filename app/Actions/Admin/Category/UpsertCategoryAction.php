<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use App\DTOs\Admin\Category\CategoryData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertCategoryAction
{
    public function execute(CategoryData $data, ?Category $category = null): Category
    {
        return DB::transaction(function () use ($data, $category) {
            $isNew = !$category;
            $pathsToClean = [];
            $attributes = $data->toArray();
            
            if (empty($attributes['slug'])) {
                $attributes['slug'] = Str::slug($data->name);
            }

            if ($data->image) {
                if ($category?->image_path) $oldImage = $category->image_path;
                $attributes['image_path'] = $data->image->store('categories/images', 'public');
                $pathsToClean[] = $attributes['image_path'];
            }

            if ($data->icon) {
                if ($category?->icon_path) $oldIcon = $category->icon_path;
                $attributes['icon_path'] = $data->icon->store('categories/icons', 'public');
                $pathsToClean[] = $attributes['icon_path'];
            }

            try {
                if ($isNew) {
                    // Algoritmo OLTP: Autocalcular el final de la cola por contexto jerárquico
                    $maxSortOrder = Category::where('parent_id', $attributes['parent_id'])
                        ->where('deleted_epoch', 0)
                        ->max('sort_order');

                    $attributes['sort_order'] = $maxSortOrder ? $maxSortOrder + 10 : 10;

                    $category = Category::create($attributes);
                } else {
                    $category->update($attributes);
                }

                if (isset($oldImage)) Storage::disk('public')->delete($oldImage);
                if (isset($oldIcon)) Storage::disk('public')->delete($oldIcon);

                return $category;

            } catch (\Exception $e) {
                foreach ($pathsToClean as $path) {
                    Storage::disk('public')->delete($path);
                }
                throw $e;
            }
        });
    }
}