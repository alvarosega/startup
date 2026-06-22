<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Category;

use App\Models\Catalog\Category;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DeleteCategoryAction
{
    public function execute(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
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