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
// app/Http/Controllers/Web/Customer/Shop/ShopController.php

    public function index(Request $request, ShopContextService $contextService, GetShopCatalogAction $catalogAction, GetShopLandingAction $landingAction) 
    {
        $branchId = $contextService->getActiveBranchId();
        
        // PILLAR C: Orquestación con Fallback (Garantizar que la landing no esté vacía)
        if (!$branchId) {
            $defaultBranch = \App\Models\Branch::where('is_active', true)->first();
            $branchId = $defaultBranch ? $defaultBranch->getRawOriginal('id') : null;
        }

        if ($request->filled('search') || $request->filled('category_id') || $request->input('type') === 'bundles') {
            $dto = CatalogQueryDTO::fromRequest($request, $branchId);
            $paginator = $catalogAction->execute($dto);
            
            return Inertia::render('Shop/Index', [
                'products' => ShopProductResource::collection($paginator),
                'filters'  => $request->only(['search', 'category_id', 'type']),
                // BLINDAJE: Convertir a Hex antes de enviar a Vue
                'context'  => ['branch_id' => $branchId ? bin2hex($branchId) : null]
            ]);
        } 

        $landingData = $landingAction->execute($branchId);

        return Inertia::render('Shop/Index', [
            'zonesData'   => $landingData['zones'],
            'bundlesData' => $landingData['bundles'],
            // BLINDAJE: El binario NUNCA debe cruzar el puente hacia Vue
            'context'     => ['branch_id' => $branchId ? bin2hex($branchId) : null]
        ]);
    }

    // app/Http/Controllers/Web/Customer/Shop/ShopController.php

    public function showZone(Request $request, MarketZone $zone, ShopContextService $contextService, GetShopZoneAction $zoneAction) 
    {
        $branchId = $contextService->getActiveBranchId();
        $groupedCategories = $zoneAction->execute($zone, $branchId);

        return Inertia::render('Shop/ZoneProducts', [
            'zone'              => $zone, // El modelo MarketZone ya debe tener el toArray corregido
            'groupedCategories' => $groupedCategories,
            'targetCategory'    => $request->input('category'),
            // BLINDAJE: Sanitización obligatoria
            'context'           => ['branch_id' => $branchId ? bin2hex($branchId) : null]
        ]);
    }
}