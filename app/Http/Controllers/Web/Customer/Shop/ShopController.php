<?php

namespace App\Http\Controllers\Web\Customer\Shop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\MarketZone;
use App\Services\ShopContextService;
// 1. Importar solo los Actions desde su carpeta
use App\Actions\Customer\Shop\{GetShopLandingAction, GetShopCatalogAction, GetShopZoneAction};
// 2. Importar los DTOs desde la carpeta DTOs (Aquí estaba el error)
use App\DTOs\Customer\Shop\{CatalogQueryDTO, LandingQueryDTO}; 
use App\Http\Resources\Customer\Shop\ShopProductResource;


class ShopController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function index(Request $request, GetShopCatalogAction $catalogAction, GetShopLandingAction $landingAction): Response
    {
        $branchId = $this->contextService->getActiveBranchId();
    
        // Modo Búsqueda / Filtros
        if ($request->anyFilled(['search', 'category_id']) || $request->input('type') === 'bundles') {
            $dto = CatalogQueryDTO::fromRequest($request, $branchId);
            return Inertia::render('Customer/Shop/Index', [
                'products' => ShopProductResource::collection($catalogAction->execute($dto)),
                'filters'  => $request->only(['search', 'category_id', 'type']),
            ]);
        } 
    
        // Modo Landing (3 Secciones)
        // Regla 2.A: Instanciación del DTO obligatoria
        $dto = LandingQueryDTO::fromRequest($branchId);
        
        $landingData = $landingAction->execute($dto); // Sincronizado con la firma de la Acción
        
        return Inertia::render('Customer/Shop/Index', [
            'zonesData'   => $landingData['zones'],
            'bundlesData' => $landingData['bundles'],
            'categories'  => $landingData['categories'], // Ahora sí fluye al componente Vue
        ]);
    }

    public function showZone(Request $request, MarketZone $zone, GetShopZoneAction $zoneAction): Response 
    {
        $branchId = $this->contextService->getActiveBranchId();

        // CAPTURAMOS EL ID DE LA MARCA: 
        // Si no viene (carga inicial), la Acción devolverá solo la navegación.
        $brandId = $request->query('brand_id');

        $data = $zoneAction->execute($zone, $branchId, $brandId);

        return Inertia::render('Customer/Shop/Zone', [
            'zone'             => $data['zone'],
            // LLAVE SINCRONIZADA: brandsNavigation en lugar de groupedCategories
            'brandsNavigation' => $data['brandsNavigation'], 
            'brandContent'     => $data['brandContent'],
            'targetCategory'   => $request->query('category'),
            'shop_context'     => ['branch_id' => $branchId] 
        ]);
    }
}