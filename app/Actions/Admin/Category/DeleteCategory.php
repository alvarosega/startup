<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{DB, Cache};

class DeleteCategoryAction
{
    /**
     * Ejecuta la eliminación lógica (Soft Delete) de una categoría
     * bajo estrictas reglas de integridad referencial.
     */
    public function execute(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            
            // 1. Regla de Integridad: Subcategorías
            if ($category->children()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Operación abortada: La categoría es padre de otras subcategorías.'
                ]);
            }

            // 2. Regla de Integridad: Productos vinculados
            // No podemos dejar productos sin categoría en el catálogo.
            if ($category->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Operación abortada: Existen productos vinculados a esta categoría.'
                ]);
            }

            // 3. Ejecución de la baja lógica
            $deleted = $category->delete();

            if ($deleted) {
                // Protocolo No-Redis: Invalidación de caché tras mutación
                $this->invalidateCache();
            }

            return $deleted;
        });
    }

    /**
     * Limpia las llaves de caché relacionadas con la estructura del catálogo.
     */
    private function invalidateCache(): void
    {
        try {
            Cache::forget('admin_category_tree');
            Cache::forget('global_categories_nav');
            Cache::forget('active_categories_global');
        } catch (\Exception $e) {
            // Reportamos pero no bloqueamos el flujo principal
            report($e);
        }
    }
}