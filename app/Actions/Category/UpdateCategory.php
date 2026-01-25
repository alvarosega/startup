<?php

namespace App\Actions\Category;

use App\DTOs\Category\CategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateCategory
{
    public function execute(Category $category, CategoryData $data): Category
    {
        $attributes = $data->toArray();

        // Auto Slug si cambió el nombre y no se especificó slug
        if (empty($attributes['slug']) && $category->name !== $attributes['name']) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        // Imagen (Reemplazo)
        if ($data->image) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $attributes['image_path'] = $data->image->store('categories', 'public');
        }

        $category->update($attributes);
        
        return $category->fresh();
    }
}