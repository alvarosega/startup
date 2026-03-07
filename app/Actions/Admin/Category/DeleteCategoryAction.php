<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Cache};

class DeleteCategoryAction
{
    public function execute(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            
            // Regla de Integridad: Productos vinculados (Mandatorio)
            if ($category->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Integridad comprometida: Existen productos vinculados a esta categoría.'
                ]);
            }

            $deleted = $category->delete();

            if ($deleted) {
                Cache::forget('admin_categories_list');
            }

            return $deleted;
        });
    }
}