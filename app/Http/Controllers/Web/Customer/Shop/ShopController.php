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
use App\Actions\Customer\Brand\GetActiveBrandsAction;
use App\Http\Resources\Customer\Brand\BrandNavResource;
class ShopController extends Controller
{
    public function __construct(protected readonly ShopContextService $contextService) {}


    public function __invoke(
        GetActiveBrandBannersAction $brandBannersAction,
        GetActiveAdCreativesAction $adAction,
        GetHomeZonesAction $zonesAction,
        GetActiveBundlesAction $bundlesAction,
        GetHomeFeaturedAction $featuredAction,
        GetTopFavoritesAction $favoritesAction,
        GetActiveBrandsAction $brandsAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();

        return Inertia::render('Customer/Shop/Index', [
            // 1. DATOS SINCRÓNICOS (Navegación inmediata)
            // Mantenemos topBrands sync si son esenciales para el primer renderizado visual
            'topBrands' => BrandNavResource::collection($brandsAction->execute())->resolve(),

            // 2. DATOS DIFERIDOS (Activan Skeletons en el Frontend)
            'brandBanners' => Inertia::defer(fn() => 
                BrandBannerResource::collection($brandBannersAction->execute($branchId))
            ),

            'featuredProducts' => Inertia::defer(fn() => 
                FeaturedProductResource::collection($featuredAction->execute($branchId))
            ),

            'bundleBanners' => Inertia::defer(fn() => 
                HeroBannerResource::collection($adAction->execute($branchId, 'BUNDLE_HERO'))
            ),

            // Agrupamos la lógica de bundles para que una sola promesa resuelva ambos filtros
            'templateBundles' => Inertia::defer(function() use ($branchId, $bundlesAction) {
                $bundles = $bundlesAction->execute($branchId);
                return BundleResource::collection($bundles->filter(fn($b) => $b->type === 'template'));
            }),

            'atomicBundles' => Inertia::defer(function() use ($branchId, $bundlesAction) {
                $bundles = $bundlesAction->execute($branchId);
                return BundleResource::collection($bundles->filter(fn($b) => $b->type === 'atomic'));
            }),

            'zonesData' => Inertia::defer(fn() => 
                $zonesAction->execute($branchId)
            ),

            'favorites' => Inertia::defer(fn() => 
                Auth::guard('customer')->check() 
                    ? FavoriteProductResource::collection($favoritesAction->execute())->resolve()
                    : []
            ),
        ]);
    }
}