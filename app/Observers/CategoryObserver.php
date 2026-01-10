<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     */
    public function creating(Category $category): void
    {
        // 1. Generación automática de Slug (solo al crear)
        if (empty($category->slug)) {
            // Str::slug convierte "Ron Abuelo" en "ron-abuelo"
            $category->slug = Str::slug($category->name);
        }
        
        // (Opcional) Evitar colisiones simples añadiendo un sufijo random si ya existe
        // Nota: Idealmente esto se maneja mejor en el Request con validación, 
        // pero aquí es un "fail-safe" de último recurso.
        if (Category::where('slug', $category->slug)->exists()) {
             $category->slug .= '-' . Str::random(4);
        }
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        // 2. Limpieza de Imágenes Viejas
        // Si el campo 'image_path' cambió (se subió una nueva), borramos la anterior.
        if ($category->isDirty('image_path')) {
            $originalImage = $category->getOriginal('image_path');
            
            if ($originalImage && Storage::disk('public')->exists($originalImage)) {
                Storage::disk('public')->delete($originalImage);
            }
        }

        // 3. Cascada de Estado (Opción C)
        // Si la categoría se desactivó, desactivamos a todos sus hijos.
        if ($category->isDirty('is_active') && !$category->is_active) {
            // update(['is_active' => false]) es una query directa, no dispara observadores en hijos 
            // (lo cual es bueno para rendimiento, evita recursividad infinita).
            $category->children()->update(['is_active' => false]);
        }
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        // 4. Limpieza Final
        // Si borramos permanentemente la categoría, borramos su imagen del disco.
        if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
            Storage::disk('public')->delete($category->image_path);
        }
    }
}