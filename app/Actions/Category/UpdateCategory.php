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

        // --- CORRECCIÓN AQUÍ ---
        // Si el slug viene vacío o nulo...
        if (empty($attributes['slug'])) {
            // A. Si el nombre cambió, generamos un slug nuevo automáticamente
            if ($attributes['name'] !== $category->name) {
                $attributes['slug'] = Str::slug($attributes['name']);
            } 
            // B. Si el nombre es el mismo, ELIMINAMOS la clave 'slug' del array
            // Esto evita que Laravel intente guardar "NULL" y mantiene el slug que ya existía.
            else {
                unset($attributes['slug']);
            }
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