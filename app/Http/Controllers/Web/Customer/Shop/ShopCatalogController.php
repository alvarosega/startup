<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopCatalogAction;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use App\Http\Resources\Customer\Shop\ShopProductResource;
use Illuminate\Http\Request;
use Inertia\{Inertia, Response};

final class ShopCatalogController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService
    ) {}

    /**
     * Motor de búsqueda y catálogo.
     * Pilar 1.D: Strict Layering (Controller -> DTO -> Action)
     */
    public function __invoke(Request $request, GetShopCatalogAction $catalogAction): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        
        // Regla 2.A: El DTO es la única puerta de entrada
        $dto = CatalogQueryDTO::fromRequest($request, $branchId);

        return Inertia::render('Customer/Shop/Catalog', [
            // Regla 2.C: Sanitización obligatoria vía Resource
            'products' => ShopProductResource::collection($catalogAction->execute($dto)),
            'filters'  => $request->only(['search', 'category_id', 'type']),
        ]);
    }
}