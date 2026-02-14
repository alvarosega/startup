<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MarketZone;
use App\Services\ShopContextService;
use App\DTOs\Shop\CatalogQueryDTO;
// --- IMPORTACIONES CORREGIDAS ---
use App\Actions\Customer\Shop\GetShopLandingAction; // <--- Correcto
use App\Actions\Customer\Shop\GetShopZoneAction;    // <--- Correcto
use App\Actions\Shop\GetShopCatalogAction; // (Este también debería moverse si quieres ser 100% estricto, pero por ahora déjalo si funciona)

use App\Http\Requests\Shop\AddToCartRequest; 
use App\DTOs\Shop\AddToCartDTO;
use App\Actions\Shop\AddItemToCartAction;
use App\Http\Requests\Shop\BulkAddToCartRequest;
use App\DTOs\Shop\BulkAddToCartDTO;
use App\Actions\Shop\AddBulkItemsToCartAction;
// -------------------------------
use App\Http\Resources\Shop\ShopProductResource;

class ShopController extends Controller
{
    public function index(
        Request $request, 
        ShopContextService $contextService, 
        GetShopCatalogAction $catalogAction,
        GetShopLandingAction $landingAction // Inyección automática
    ) {
        $branchId = $contextService->getActiveBranchId();
        
        if ($request->filled('search') || $request->filled('category_id') || $request->input('type') === 'bundles') {
             // ... lógica de búsqueda ...
             $dto = CatalogQueryDTO::fromRequest($request, $branchId);
             $paginator = $catalogAction->execute($dto);
             // ...
             return Inertia::render('Shop/Index', [ /* ... */ ]);
        } 
        
        // Ejecutamos la acción desde el namespace Customer
        $landingData = $landingAction->execute($branchId);

        return Inertia::render('Shop/Index', [
            'zonesData' => $landingData['zones'],
            'bundlesData' => $landingData['bundles'],
            'context' => ['branch_id' => $branchId]
        ]);
    }

    public function showZone(
        Request $request, 
        MarketZone $zone, 
        ShopContextService $contextService,
        GetShopZoneAction $zoneAction // Inyección automática
    ) {
        $branchId = $contextService->getActiveBranchId();

        // Ejecutamos la acción desde el namespace Customer
        $groupedCategories = $zoneAction->execute($zone, $branchId);

        return Inertia::render('Shop/ZoneProducts', [
            'zone' => $zone,
            'groupedCategories' => $groupedCategories,
            'targetCategory' => $request->input('category'),
            'context' => ['branch_id' => $branchId]
        ]);
    }
}