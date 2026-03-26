<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\MarketZone;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\{GetShopCatalogAction, GetShopZoneAction};
use App\DTOs\Customer\Shop\CatalogQueryDTO; 
use App\Http\Resources\Customer\Shop\ShopProductResource;
use App\Actions\Customer\RetailMedia\GetActiveAdCreativesAction;
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;

class ShopController extends Controller
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    /**
     * Motor de Búsqueda y Catálogo
     */
    public function index(
        Request $request, 
        GetShopCatalogAction $catalogAction,
        GetActiveAdCreativesAction $adAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
    
        // 1. Ejecución del Catálogo
        $dto = CatalogQueryDTO::fromRequest($request, $branchId);
        $products = $catalogAction->execute($dto);

        // 2. Publicidad Contextual para Búsqueda (Opcional)
        $heroBanners = $adAction->execute($branchId, 'SEARCH_HERO');

        return Inertia::render('Customer/Shop/Search', [
            'products' => ShopProductResource::collection($products),
            'filters'  => $request->only(['search', 'category_id', 'type']),
            'heroBanners' => HeroBannerResource::collection($heroBanners),
        ]);
    }

    /**
     * Vista de Zona de Mercado (Pasillos)
     */
    public function showZone(
        Request $request, 
        MarketZone $zone, 
        GetShopZoneAction $zoneAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        $brandId = $request->query('brand_id');

        $data = $zoneAction->execute($zone, $branchId, $brandId);

        return Inertia::render('Customer/Shop/Zone', [
            'zone'             => $data['zone'],
            'brandsNavigation' => $data['brandsNavigation'], 
            'brandContent'     => $data['brandContent'],
            'targetCategory'   => $request->query('category'),
            'shop_context'     => ['branch_id' => $branchId] 
        ]);
    }
}