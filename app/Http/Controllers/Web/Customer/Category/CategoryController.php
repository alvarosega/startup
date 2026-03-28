<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Category;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Category\GetCategoryDetailsAction;
use App\Actions\Customer\Product\ListCategoryProductsAction;
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
        string $category, // Parámetro que viene de la ruta {category:slug}
        Request $request, 
        GetCategoryDetailsAction $action, 
        ListCategoryProductsAction $listAction
    ): Response {
        $deviceType = $request->header('X-Device-Type', 'desktop');
        
        // CORRECCIÓN QUIRÚRGICA: Usamos $category (el slug real)
        $categoryDTO = $action->execute($category, $deviceType);
        $branchId = $this->contextService->getActiveBranchId();
    
        return Inertia::render('Customer/Category/Show', [
            'categoryData' => new CategoryResource($categoryDTO),
            
            'products' => $listAction->execute($categoryDTO->id, $branchId, [
                'search' => $request->query('search'),
                'sort'   => $request->query('sort')
            ]),
            
            'filters' => $request->only(['search', 'sort'])
        ]);
    }
}