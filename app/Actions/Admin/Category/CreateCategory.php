<?php

namespace App\Actions\Admin\Category; // Namespace corregido

use App\DTOs\Admin\Category\CategoryData;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateCategory
{
    public function execute(CategoryData $data): Category
    {
        return \DB::transaction(function () use ($data) {
            $attributes = $data->toArray();
            $attributes['slug'] = $data->slug ?: \Str::slug($data->name);
            
            $pathsToClean = []; // Tracking de archivos para revertir si falla la DB
    
            if ($data->image) {
                $path = $data->image->store('categories', 'public');
                $attributes['image_path'] = $path;
                $pathsToClean[] = $path;
            }
    
            try {
                $parent = Category::create($attributes);
    
                // Procesar hijos con sus propias imágenes
                $childrenData = request()->file('children', []); // Extraemos archivos directamente del request
    
                foreach ($data->children as $index => $child) {
                    $childAttributes = [
                        'name' => $child['name'],
                        'slug' => \Str::slug($child['name']),
                        'external_code' => $child['external_code'] ?? null,
                        'is_active' => $parent->is_active,
                    ];
    
                    // Si este hijo tiene una imagen cargada
                    if (isset($childrenData[$index]['image'])) {
                        $cPath = $childrenData[$index]['image']->store('categories', 'public');
                        $childAttributes['image_path'] = $cPath;
                        $pathsToClean[] = $cPath;
                    }
    
                    $parent->children()->create($childAttributes);
                }
    
                return $parent;
    
            } catch (\Exception $e) {
                foreach ($pathsToClean as $path) {
                    \Storage::disk('public')->delete($path);
                }
                throw $e;
            }
        });
    }
}