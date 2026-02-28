<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\MarketZone;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\{GetShopLandingAction, GetShopCatalogAction, GetShopZoneAction};
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use App\Http\Resources\Customer\Shop\ShopProductResource;

class ShopController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function index(Request $request, GetShopCatalogAction $catalogAction, GetShopLandingAction $landingAction): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
    
        if ($request->anyFilled(['search', 'category_id']) || $request->input('type') === 'bundles') {
            $dto = CatalogQueryDTO::fromRequest($request, $branchId);
            return Inertia::render('Customer/Shop/Index', [
                'products' => ShopProductResource::collection($catalogAction->execute($dto)),
                'filters'  => $request->only(['search', 'category_id', 'type']),
            ]);
        } 
    
        $landingData = $landingAction->execute($branchId);
        return Inertia::render('Customer/Shop/Index', [
            'zonesData'   => $landingData['zones'],
            'bundlesData' => $landingData['bundles'],
        ]);
    }

    public function showZone(Request $request, MarketZone $zone, GetShopZoneAction $zoneAction): Response 
    {
        $branchId = $this->contextService->getActiveBranchId();

        // El Action se encarga de todo el procesamiento pesado y la jerarquía
        $data = $zoneAction->execute($zone, $branchId);

        return Inertia::render('Customer/Shop/Zone', [
            'zone'              => $data['zone'],
            'groupedCategories' => $data['groupedCategories'],
            'targetCategory'    => $request->query('category'),
            'shop_context'      => ['branch_id' => $branchId] 
        ]);
    }
}