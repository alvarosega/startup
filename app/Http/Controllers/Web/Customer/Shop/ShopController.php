<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\MarketZone;
use App\Models\Branch;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopLandingAction;
use App\Actions\Customer\Shop\GetShopZoneAction;
use App\Actions\Customer\Shop\GetShopCatalogAction;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use App\Http\Resources\Customer\Shop\ShopProductResource;

class ShopController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function index(Request $request, GetShopCatalogAction $catalogAction, GetShopLandingAction $landingAction): Response
    {
        // El servicio garantiza un UUID válido o lanza excepción si la DB está vacía
        $branchId = $this->contextService->getActiveBranchId();
    
        if ($request->anyFilled(['search', 'category_id']) || $request->input('type') === 'bundles') {
            $dto = CatalogQueryDTO::fromRequest($request, $branchId);
            $paginator = $catalogAction->execute($dto);
            
            return Inertia::render('Customer/Shop/Index', [
                'products' => ShopProductResource::collection($paginator),
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
        $branchId = $this->resolveBranchId();

        if (!$branchId) {
            return redirect()->route('customer.shop.index')
                ->withErrors(['error' => 'Contexto de tienda no disponible.']);
        }

        $groupedCategories = $zoneAction->execute($zone, $branchId);

        return Inertia::render('Customer/Shop/Zone', [
            'zone'              => $zone,
            'groupedCategories' => $groupedCategories,
            'targetCategory'    => $request->query('category'),
            'shop_context'      => ['branch_id' => $branchId] 
        ]);
    }

    /**
     * Centraliza la resolución de BranchId para evitar TypeErrors.
     */
    private function resolveBranchId(): ?string
    {
        $branchId = $this->contextService->getActiveBranchId();

        if (!$branchId) {
            // Fallback a la matriz o primera sucursal activa
            $branchId = Branch::where('is_active', true)->first()?->id;
            
            if ($branchId) {
                // Persistimos el fallback en el contexto para futuras peticiones
                $this->contextService->setContext($branchId);
            }
        }

        return $branchId;
    }
}