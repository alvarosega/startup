<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DeleteCategoryAction
{
    public function execute(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            
            // Protección Estricta contra la desarticulación de pasillos
            if ($category->children()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Operación denegada: Este nodo posee subcategorías dependientes asignadas.'
                ]);
            }

            if ($category->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Integridad comprometida: Existen productos vinculados a esta categoría.'
                ]);
            }

            return (bool) $category->delete();
        });
    }
}