<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopCatalogAction;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use App\Http\Resources\Customer\Shop\ShopProductResource;
use Inertia\Inertia;
use Inertia\Response;

class ShopCatalogController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function __invoke(Request $request, GetShopCatalogAction $catalogAction): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
        $dto = CatalogQueryDTO::fromRequest($request, $branchId);

        // Retorna a una vista de catálogo independiente
        return Inertia::render('Customer/Shop/Catalog', [
            'products' => ShopProductResource::collection($catalogAction->execute($dto)),
            'filters'  => $request->only(['search', 'category_id', 'type']),
        ]);
    }
}