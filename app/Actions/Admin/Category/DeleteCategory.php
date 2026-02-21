<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class DeleteCategory
{
    public function execute(Category $category): bool
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar: la categoría tiene subcategorías vinculadas.'
            ]);
        }

        return $category->delete();
    }
}