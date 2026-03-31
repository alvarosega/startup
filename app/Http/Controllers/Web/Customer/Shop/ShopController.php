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
use Illuminate\Support\Facades\Auth; // RECTIFICACIÓN: Importación obligatoria
use App\Actions\Customer\Favorites\GetTopFavoritesAction; // <--- ASEGURAR IMPORT
// RESOURCES
use App\Http\Resources\Customer\Brand\BrandBannerResource;
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use App\Http\Resources\Customer\Bundle\BundleResource;
use App\Actions\Customer\Featured\GetHomeFeaturedAction;
use App\Http\Resources\Customer\Featured\FeaturedProductResource;
use App\Http\Resources\Customer\Featured\ProductShowcaseResource; 

class ShopController extends Controller
{
    public function __construct(protected readonly ShopContextService $contextService) {}

    public function __invoke(
        GetActiveBrandBannersAction $brandBannersAction,
        GetActiveAdCreativesAction $adAction,
        GetHomeZonesAction $zonesAction,
        GetActiveBundlesAction $bundlesAction,
        GetHomeFeaturedAction $featuredAction 
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        $isLoggedIn = Auth::guard('customer')->check();
        return Inertia::render('Customer/Shop/Index', [
            // Banners de Marca para el Carrusel de la Home
            'brandBanners'  => BrandBannerResource::collection($brandBannersAction->execute($branchId)),
            'featuredProducts' => FeaturedProductResource::collection($featuredAction->execute()),
            'bundleBanners' => HeroBannerResource::collection($adAction->execute($branchId, 'BUNDLE_HERO')),
            'zonesData'     => $zonesAction->execute($branchId), 
            'bundlesData' => BundleResource::collection($bundlesAction->execute($branchId)),
            'favorites' => Auth::guard('customer')->check() 
                ? ProductShowcaseResource::collection($favoritesAction->execute())
                : [],

        ]);
    }
}