<?php

namespace App\Actions\Category;

use App\DTOs\Category\CategoryData;
use App\Models\Category;
use Illuminate\Support\Str;

class CreateCategory
{
    public function execute(CategoryData $data): Category
    {
        $attributes = $data->toArray();

        // Auto Slug
        if (empty($attributes['slug'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        // Imagen
        if ($data->image) {
            $attributes['image_path'] = $data->image->store('categories', 'public');
        }

        return Category::create($attributes);
    }
}