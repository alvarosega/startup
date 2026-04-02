<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use Inertia\{Inertia, Response};
use Illuminate\Support\Facades\Auth;

// ACTIONS
use App\Actions\Customer\Brand\GetActiveBrandBannersAction;
use App\Actions\Customer\RetailMedia\GetActiveAdCreativesAction;
use App\Actions\Customer\Shop\GetHomeZonesAction;
use App\Actions\Customer\Bundle\GetActiveBundlesAction;
use App\Actions\Customer\Featured\GetHomeFeaturedAction;
use App\Actions\Customer\Favorites\GetTopFavoritesAction;

// RESOURCES
use App\Http\Resources\Customer\Brand\BrandBannerResource;
use App\Http\Resources\Customer\RetailMedia\HeroBannerResource;
use App\Http\Resources\Customer\Bundle\BundleResource;
use App\Http\Resources\Customer\Featured\FeaturedProductResource;
use App\Http\Resources\Customer\Favorite\FavoriteProductResource; // <--- USAR ESTE

class ShopController extends Controller
{
    public function __construct(protected readonly ShopContextService $contextService) {}

    public function __invoke(
        GetActiveBrandBannersAction $brandBannersAction,
        GetActiveAdCreativesAction $adAction,
        GetHomeZonesAction $zonesAction,
        GetActiveBundlesAction $bundlesAction,
        GetHomeFeaturedAction $featuredAction,
        GetTopFavoritesAction $favoritesAction // <--- Inyección validada
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        
        return Inertia::render('Customer/Shop/Index', [
            'brandBanners'     => BrandBannerResource::collection($brandBannersAction->execute($branchId)),
            'featuredProducts' => FeaturedProductResource::collection($featuredAction->execute()),
            'bundleBanners'    => HeroBannerResource::collection($adAction->execute($branchId, 'BUNDLE_HERO')),
            'zonesData'        => $zonesAction->execute($branchId), 
            'bundlesData'      => BundleResource::collection($bundlesAction->execute($branchId)),
            
            // ACLARACIÓN: Resolvemos la colección para que llegue como Array [] y no como Objeto {}
            'favorites' => Auth::guard('customer')->check() 
                ? FavoriteProductResource::collection($favoritesAction->execute())->resolve()
                : [],
        ]);
    }
}