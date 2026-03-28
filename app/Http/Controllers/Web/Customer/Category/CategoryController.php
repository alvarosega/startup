<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Category;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Category\GetCategoryDetailsAction;
use App\Actions\Customer\Product\ListCategoryProductsAction; // INYECCIÓN NECESARIA
use App\Http\Resources\Customer\Category\CategoryResource;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Inertia\{Inertia, Response};

class CategoryController extends Controller
{
    public function __construct(
        private ShopContextService $contextService
    ) {}

    public function __invoke(
        string $category, // CAMBIADO: de $slug a $category para match con web.php
        Request $request, 
        GetCategoryDetailsAction $action, 
        ListCategoryProductsAction $listAction
    ): Response {
        $deviceType = $request->header('X-Device-Type', 'desktop');
        
        // El Action ahora debe devolver la categoría cargada con sus AdCreatives (banners)
        $categoryDTO = $action->execute($slug, $deviceType);
        $branchId = $this->contextService->getActiveBranchId();
    
        return Inertia::render('Customer/Category/Show', [
            // Pasamos el recurso polimórfico. Vue usará categoryData.id como el activeId del carrusel.
            'categoryData' => new CategoryResource($categoryDTO),
            
            'products' => $listAction->execute($categoryDTO->id, $branchId, [
                'search' => $request->query('search'),
                'sort'   => $request->query('sort')
            ]),
            
            // VITAL: Mantenemos el estado de los filtros para que el carrusel no se resetee al filtrar
            'filters' => $request->only(['search', 'sort'])
        ]);
    }
}