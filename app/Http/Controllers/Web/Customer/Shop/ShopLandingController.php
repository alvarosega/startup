<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\DTOs\Customer\Shop\LandingQueryDTO;
use App\Actions\Customer\RetailMedia\GetActiveAdCreativesAction;
use App\Actions\Customer\Shop\GetShopLandingAction; 
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use Inertia\{Inertia, Response};

final class ShopLandingController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService
    ) {}

    public function __invoke(
        GetActiveAdCreativesAction $adAction,
        GetShopLandingAction $landingAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        $dto = LandingQueryDTO::fromRequest($branchId);
        
        // Ejecución de Silos Publicitarios
        $heroBanners = $adAction->execute($branchId, 'HOME_HERO');
        $bundlePromos = $adAction->execute($branchId, 'BUNDLE_PROMO');

        // Ejecución de Silos de Catálogo
        $landingData = $landingAction->execute($dto);

        return Inertia::render('Customer/Shop/Index', [
            // Carrusel superior
            'heroBanners'   => HeroBannerResource::collection($heroBanners),
            
            // Modal de promociones (Bundle Banners)
            'bundleBanners' => HeroBannerResource::collection($bundlePromos),
            
            // Datos de estructura (Zonas, Categorías, Bundles)
            'zonesData'     => $landingData['zones'],
            'categories'    => $landingData['categories'],
            'bundlesData'   => $landingData['bundles'],
        ]);
    }
}