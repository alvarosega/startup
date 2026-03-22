<?php

namespace App\Http\Controllers\Web\Customer\Shop\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Actions\Customer\Shop\Category\GetCategoryPageAction;
use App\Http\Resources\Customer\Shop\Category\CategoryPageResource;
use App\Http\Resources\Customer\Shop\LandingCategoryResource;
use App\DTOs\Customer\Shop\Category\CategoryPageDTO;
use App\Services\ShopContextService;
use Illuminate\Http\Request; // <--- CORRECCIÓN CRÍTICA: Importación de Request
use Inertia\{Inertia, Response};

class CategoryController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    /**
     * Pilar 3.A: Orquestador de Góndola Digital.
     * Integra búsqueda reactiva, merchandising estratégico y Redis.
     */
    public function __invoke(Request $request, Category $category, GetCategoryPageAction $action): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        $searchTerm = $request->query('search'); // Captura el parámetro del buscador
        
        // Generamos el DTO de transporte
        $dto = CategoryPageDTO::fromRequest($branchId, $category->id);
        
        // Ejecutamos la acción (con soporte de Redis y Búsqueda)
        $data = $action->execute($dto, $searchTerm);

        return Inertia::render('Customer/Shop/Category/Show', [
            'categoryData' => new CategoryPageResource([
                'category' => $category,
                'banners'  => $data['banners'] ?? [],
                'products' => $data['products']
            ]),
            // Cargamos el navegador global desde la Action (ya viene de caché)
            'categories' => LandingCategoryResource::collection($data['all_categories']),
            'filters'    => [
                'search' => $searchTerm
            ]
        ]);
    }
}