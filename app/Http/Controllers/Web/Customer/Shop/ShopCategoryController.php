<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetCategoryProductsAction;
use Inertia\Inertia;
use Inertia\Response;

class ShopCategoryController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function __invoke(Category $category, GetCategoryProductsAction $action): Response 
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        // Delegamos a la acción la obtención de productos con sus precios reales
        $products = $action->execute($category->id, $branchId);

        return Inertia::render('Customer/Shop/Category', [
            'category' => [
                'id'       => $category->id,
                'name'     => $category->name,
                'bg_color' => $category->bg_color
            ],
            'products' => $products
        ]);
    }
}