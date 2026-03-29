<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use Inertia\{Inertia, Response};

// ACCIÓN CORREGIDA PARA HOME
use App\Actions\Customer\Brand\GetActiveBrandBannersAction;
use App\Actions\Customer\RetailMedia\GetActiveAdCreativesAction;
use App\Actions\Customer\Shop\GetHomeZonesAction;
use App\Actions\Customer\Bundle\GetActiveBundlesAction;

// RESOURCES
use App\Http\Resources\Customer\Brand\BrandBannerResource;
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use App\Http\Resources\Customer\Bundle\BundleResource;

class ShopController extends Controller
{
    public function __construct(protected readonly ShopContextService $contextService) {}

    public function __invoke(
        GetActiveBrandBannersAction $brandBannersAction,
        GetActiveAdCreativesAction $adAction,
        GetHomeZonesAction $zonesAction,
        GetActiveBundlesAction $bundlesAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();

        return Inertia::render('Customer/Shop/Index', [
            // Banners de Marca para el Carrusel de la Home
            'brandBanners'  => BrandBannerResource::collection($brandBannersAction->execute($branchId)),
            
            'bundleBanners' => HeroBannerResource::collection($adAction->execute($branchId, 'BUNDLE_HERO')),
            'zonesData'     => $zonesAction->execute($branchId), 
            'bundlesData' => BundleResource::collection($bundlesAction->execute($branchId)),

        ]);
    }
}