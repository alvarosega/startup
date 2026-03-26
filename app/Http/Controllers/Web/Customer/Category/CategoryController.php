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

    // MODIFICAR el método __invoke para que quede así:
    public function __invoke(string $slug, Request $request, GetCategoryDetailsAction $action, ListCategoryProductsAction $listAction): Response
    {
        $deviceType = $request->header('X-Device-Type', 'desktop');
        $categoryDTO = $action->execute($slug, $deviceType);
        $branchId = $this->contextService->getActiveBranchId();

        return Inertia::render('Customer/Category/Show', [
            'categoryData' => new CategoryResource($categoryDTO),
            
            // RECTIFICACIÓN: Ejecución directa para carga inicial. 
            // Usamos la acción que busca SKUs por categoría (incluyendo subcategorías).
            'products' => $listAction->execute($categoryDTO->id, $branchId, [
                'search' => $request->query('search'),
                'sort'   => $request->query('sort')
            ]),
            
            'filters' => $request->only(['search', 'sort'])
        ]);
    }
}