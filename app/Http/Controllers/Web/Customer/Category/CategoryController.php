<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Category;

use App\Http\Controllers\Controller;
use App\Actions\Customer\Category\GetCategoryDetailsAction;
use App\Actions\Customer\Sku\ListSkusAction; 
use App\Http\Resources\Customer\Category\CategoryResource;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Inertia\{Inertia, Response};
use App\Http\Resources\Customer\Category\CategoryBannerResource;
use App\Http\Resources\Customer\Sku\SkuStateResource;
use App\Http\Resources\Customer\Sku\SkuResource;

class CategoryController extends Controller
{
    public function __construct(
        private ShopContextService $contextService
    ) {}

// app/Http/Controllers/Web/Customer/Category/CategoryController.php

public function __invoke(
    string $category, 
    Request $request, 
    GetCategoryDetailsAction $action, 
    ListSkusAction $listAction
    ): Response {
        $deviceType = $request->header('X-Device-Type', 'desktop');
        $branchId = $this->contextService->getActiveBranchId();
        $customerId = \Illuminate\Support\Facades\Auth::guard('customer')->id();

        // 1. OBTENCIÓN DE METADATOS (Sincrónico)
        // Esto es rápido y nos da el ID necesario para las promesas posteriores.
        $categoryDTO = $action->execute($category, $deviceType);

        return Inertia::render('Customer/Category/Show', [
            // Metadatos rápidos: se envían de inmediato.
            'categoryData' => new CategoryResource($categoryDTO),
            
            // DEFER: Los banners se cargan en segundo plano.
            'banners' => Inertia::defer(fn() => 
                CategoryBannerResource::collection($categoryDTO->banners ?? [])
            ),
            
            // DEFER: El listado de productos (lo más pesado) activa el Skeleton Grid.
            'products' => Inertia::defer(fn() => 
                SkuResource::collection(
                    $listAction->execute(
                        $categoryDTO->id, 
                        $branchId, 
                        $customerId, 
                        $request->only(['search', 'sort'])
                    )
                )
            ),
            
            'filters' => $request->only(['search', 'sort'])
        ]);
    }
}