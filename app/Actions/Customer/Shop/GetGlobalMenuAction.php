<?php
declare(strict_types=1);

namespace App\Actions\Customer\Shop;

use App\Models\Category;
use App\Http\Resources\Customer\Shop\LandingCategoryResource;
use Illuminate\Support\Facades\Cache;

/**
 * ACCIÓN ATÓMICA: Obtención del Menú Global de Categorías.
 * Objetivo: Cero latencia en el renderizado del Layout.
 */
class GetGlobalMenuAction
{
    /**
     * Ejecuta la recuperación de categorías raíz.
     * * @return array<int, array> Datos transformados por el Resource.
     */
    public function execute(): array
    {
        // TTL: 24 horas (86400s). El Admin purga esta llave al editar categorías.
        return Cache::remember('customer_global_menu_v1', 86400, function () {
            $categories = Category::query()
                ->select([
                    'id', 
                    'name', 
                    'slug', 
                    'image_path', 
                    'bg_color', 
                    'sort_order'
                ])
                ->where('is_active', true)
                ->whereNull('parent_id') // Solo departamentos raíz
                ->orderBy('sort_order', 'asc')
                ->get();

            // Resolvemos la colección a un array primitivo para la caché
            return LandingCategoryResource::collection($categories)->resolve();
        });
    }
}