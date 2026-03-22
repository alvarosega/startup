<?php

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\DTOs\Customer\Shop\LandingQueryDTO;
// IMPORTACIONES CRÍTICAS (Asegúrate de que estas rutas existan)
use App\Actions\Customer\RetailMedia\GetActiveHeroBannersAction;
use App\Actions\Customer\Shop\GetShopLandingAction; 
// RESOURCES
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use Inertia\{Inertia, Response};

final class ShopLandingController extends Controller
{
    public function __construct(
        private readonly ShopContextService $contextService
    ) {}

    public function __invoke(
        GetActiveHeroBannersAction $bannerAction,
        GetShopLandingAction $landingAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        
        // Regla 2.A: Uso de DTO obligatorio
        $dto = LandingQueryDTO::fromRequest($branchId);
        
        // Ejecución atómica de silos
        $banners = $bannerAction->execute($branchId);
        $landingData = $landingAction->execute($dto);

        return Inertia::render('Customer/Shop/Index', [
            'heroBanners' => HeroBannerResource::collection($banners),
            // Pasamos los datos del landing action (Zonas, Categorías, Bundles)
            'zonesData'   => $landingData['zones'],
            'categories'  => $landingData['categories'],
            'bundlesData' => $landingData['bundles'],
        ]);
    }
}