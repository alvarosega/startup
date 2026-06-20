<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Brand;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Models\Brand;
use Inertia\Inertia;
// ACCIONES FRACTURADAS
use App\Actions\Customer\Brand\ListBrandNavigationAction;
use App\Actions\Customer\Brand\GetBrandHeroAction;
use App\Actions\Customer\Brand\GetBrandSkuAction;

// RESOURCES & REQUEST
use App\Http\Requests\Customer\Brand\BrandCatalogRequest;
use App\Http\Resources\Customer\Brand\{BrandNavResource, BrandHeroResource, BrandDetailResource};
use App\Http\Resources\Customer\Sku\SkuResource;
use Inertia\Response;

class BrandController extends Controller
{
    public function __construct(
        protected readonly ShopContextService $contextService
    ) {}
    public function show(
        string $slug, 
        BrandCatalogRequest $request,
        ListBrandNavigationAction $navAction,
        GetBrandHeroAction $heroAction,
        GetBrandSkuAction $skuAction
    ): Response {
        $branchId = $this->contextService->getActiveBranchId();
        
        // 1. Identificar Marca Raíz
        $brand = Brand::where('slug', $slug)->active()->firstOrFail();
        
        // 2. Blindaje de variables para los closures diferidos
        $brandId = (string) $brand->id;
        $filters = $request->validated();

        return inertia('Customer/Brand/Show', [
            'currentBrand' => new BrandDetailResource($brand),
            
            // DEFER: Navegación de marcas (Ejecución única)
            'brandNav' => Inertia::defer(fn() => 
                BrandNavResource::collection($navAction->execute())
            ),
    
            // DEFER: Banner Hero (Ejecución única)
            'brandHero' => Inertia::defer(fn() => 
                ($hero = $heroAction->execute($brandId, $branchId)) 
                    ? new BrandHeroResource($hero) 
                    : null
            ),
    
            // DEFER: Listado de productos con filtros capturados
            'products' => Inertia::defer(fn() => 
                SkuResource::collection($skuAction->execute($brandId, $branchId, $filters))
            ),
    
            'filters' => $filters
        ]);
    }
}