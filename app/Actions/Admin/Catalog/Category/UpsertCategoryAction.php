<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Category;

use App\Models\Catalog\Category;
use App\DTOs\Admin\Catalog\Category\CategoryData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{DB, Storage};

class UpsertCategoryAction
{
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

        DB::beginTransaction();
        try {
            if ($isNew) {
                $maxSortOrder = Category::where('parent_id', $attributes['parent_id'])
                    ->where('deleted_epoch', 0)
                    ->max('sort_order');

                $attributes['sort_order'] = $maxSortOrder ? $maxSortOrder + 10 : 10;
                $category = Category::create($attributes);
            } else {
                $category->update($attributes);
            }

            DB::commit();

            // Los efectos secundarios en el almacenamiento físico ocurren estrictamente post-commit
            if (isset($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            if (isset($oldIcon)) {
                Storage::disk('public')->delete($oldIcon);
            }

            return $category;

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si la base de datos falla, se eliminan del disco los nuevos archivos huérfanos
            foreach ($pathsToClean as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}