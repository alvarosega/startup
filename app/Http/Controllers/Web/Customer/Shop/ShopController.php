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
        GetActiveBundlesAction $bundlesAction, // <--- La acción está inyectada pero no se usó
        GetHomeFeaturedAction $featuredAction,
        GetTopFavoritesAction $favoritesAction,
        GetActiveBrandsAction $brandsAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
    
        // 1. OBTENCIÓN NOMINAL DE DATOS (Faltaba esta línea)
        $allBundles = $bundlesAction->execute($branchId);
    
        // 2. SEGMENTACIÓN TÁCTICA (Ahora sí existe la variable)
        // Usamos el Higher Order Filter de Eloquent Collection
        $templates = $allBundles->filter(fn($b) => $b->type === 'template');
        $atomic    = $allBundles->filter(fn($b) => $b->type === 'atomic');
    
        return Inertia::render('Customer/Shop/Index', [
            'topBrands'        => BrandNavResource::collection($brandsAction->execute())->resolve(),
            'brandBanners'     => BrandBannerResource::collection($brandBannersAction->execute($branchId)),
            'featuredProducts' => FeaturedProductResource::collection($featuredAction->execute($branchId)),
            'bundleBanners'    => HeroBannerResource::collection($adAction->execute($branchId, 'BUNDLE_HERO')),
            'zonesData'        => $zonesAction->execute($branchId), 
            
            // RECTIFICACIÓN DE PROPS: Segmentación para el frontend
            'templateBundles'  => BundleResource::collection($templates), 
            'atomicBundles'    => BundleResource::collection($atomic),
            
            // ELIMINACIÓN: 'bundlesData' ya no es necesario si segmentamos arriba
            
            'favorites' => Auth::guard('customer')->check() 
                ? FavoriteProductResource::collection($favoritesAction->execute())->resolve()
                : [],
        ]);
    }
}