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
            $pathsToClean = [];

            $attributes = $data->toArray();
            
            // Forzar slug si está vacío
            if (empty($attributes['slug'])) {
                $attributes['slug'] = Str::slug($data->name);
            }

            // Gestión de Assets con rollback manual en caso de fallo de DB
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
                    $category = Category::create($attributes);
                } else {
                    $category->update($attributes);
                }

                if (isset($oldImage)) Storage::disk('public')->delete($oldImage);
                if (isset($oldIcon)) Storage::disk('public')->delete($oldIcon);

                return $category;

            } catch (\Exception $e) {
                // Si la DB falla, borramos las imágenes que acabamos de subir
                foreach ($pathsToClean as $path) Storage::disk('public')->delete($path);
                throw $e;
            }
        });
    }
}