<?php

namespace App\Actions\Admin\Category; // Namespace corregido

use App\DTOs\Admin\Category\CategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateCategory
{
    public function execute(Category $category, CategoryData $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $attributes = $data->toArray();
            $oldPath = $category->image_path;
            $newPath = null;

            if (empty($attributes['slug'])) {
                if ($attributes['name'] !== $category->name) {
                    $attributes['slug'] = Str::slug($attributes['name']);
                } else {
                    unset($attributes['slug']);
                }
            }

            if ($data->image) {
                $newPath = $data->image->store('categories', 'public');
                $attributes['image_path'] = $newPath;
            }

            try {
                $category->update($attributes);
                if ($newPath && $oldPath) Storage::disk('public')->delete($oldPath);
                return $category->fresh();
            } catch (\Exception $e) {
                if ($newPath) Storage::disk('public')->delete($newPath);
                throw $e;
            }
        });
    }
}