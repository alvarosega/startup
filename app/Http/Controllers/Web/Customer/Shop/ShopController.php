<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Inertia\{Inertia, Response};

// SILOS DE ACCIONES (Aislamiento Total)
use App\Actions\Customer\RetailMedia\GetActiveAdCreativesAction;
use App\Actions\Customer\Shop\GetHomeZonesAction;
use App\Actions\Customer\Bundle\GetActiveBundlesAction;

// RESOURCES
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use App\Models\MarketZone;

class ShopController extends Controller
{
    public function __construct(
        protected readonly ShopContextService $contextService
    ) {}


    public function __invoke(
        GetActiveAdCreativesAction $adAction,
        GetHomeZonesAction $zonesAction,
        GetActiveBundlesAction $bundlesAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();

        return Inertia::render('Customer/Shop/Index', [
            // Silo 1: Retail Media (Publicidad)
            'heroBanners'   => HeroBannerResource::collection($adAction->execute($branchId, 'HOME_HERO')),
            'bundleBanners' => HeroBannerResource::collection($adAction->execute($branchId, 'BUNDLE_PROMO')),
            
            // Silo 2: Zonas de Productos (Alta Densidad)
            'zonesData'     => $zonesAction->execute($branchId), 
            
            // Silo 3: Bundles / Packs Activos
            'bundlesData'   => $bundlesAction->execute($branchId),
        ]);
    }

    /**
     * VISTA DE ZONA ESPECÍFICA (Pasillos)
     */
    public function showZone(
        Request $request, 
        MarketZone $zone, 
        GetShopZoneAction $zoneAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        $brandId = $request->query('brand_id');

        $data = $zoneAction->execute($zone, $branchId, (string)$brandId);

        return Inertia::render('Customer/Shop/Zone', [
            'zone'             => $data['zone'],
            'brandsNavigation' => $data['brandsNavigation'], 
            'brandContent'     => $data['brandContent'],
            'targetCategory'   => $request->query('category'),
            'shop_context'     => ['branch_id' => $branchId] 
        ]);
    }
}