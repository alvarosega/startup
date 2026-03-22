<?php

namespace App\Http\Controllers\Web\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Models\{Brand, Provider, Category, MarketZone};
use App\Actions\Admin\Brand\{ListBrands, UpsertBrandAction, GetBrandStatsAction, DeleteBrandAction};
use App\DTOs\Admin\Brand\BrandData;
use App\Http\Requests\Admin\Brand\{StoreBrandRequest, UpdateBrandRequest};
use App\Http\Resources\Admin\Brand\BrandResource;
use Illuminate\Http\{Request, RedirectResponse};
use Inertia\{Inertia, Response};
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;


class BrandController extends Controller
{
    use AuthorizesRequests;
    
    private string $guard = 'super_admin';

    public function index(ListBrands $listAction, GetBrandStatsAction $statsAction): Response
    {
        $this->authorize('viewAny', Brand::class);

        return Inertia::render('Admin/Brands/Index', [
            'brands'  => BrandResource::collection($listAction->execute(request()->all())),
            'stats'   => $statsAction->execute(),
            'options' => [
                'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as name']),
                'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
                'zones'      => MarketZone::active()->get(['id', 'name']),
                'parents'    => Brand::whereNull('parent_id')->get(['id', 'name']), // Soporte Sub-marcas
            ]
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Brand::class);
        return Inertia::render('Admin/Brands/Create', [
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as company_name']),
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'zones'      => MarketZone::getMinimalList()
        ]);
    }

    public function edit(Brand $brand): Response
    {
        $this->authorize('update', $brand);
        return Inertia::render('Admin/Brands/Edit', [
            'brand'      => new BrandResource($brand),
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as company_name']),
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'zones'      => MarketZone::getMinimalList()
        ]);
    }
            
    public function store(StoreBrandRequest $request, UpsertBrandAction $action): RedirectResponse
    {
        $action->execute(BrandData::fromRequest($request));
        return redirect()->route('admin.brands.index')->with('message', 'Operación exitosa.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpsertBrandAction $action): RedirectResponse
    {
        $this->authorize('update', $brand);
        $action->execute(BrandData::fromRequest($request), $brand);
        return redirect()->route('admin.brands.index')->with('message', 'Marca actualizada.');
    }

    public function destroy(Brand $brand, DeleteBrandAction $action): RedirectResponse
    {
        $this->authorize('delete', $brand);
        
        try {
            // DELEGAR: El controlador orquesta, la Action ejecuta.
            $action->execute($brand);
            return redirect()->route('admin.brands.index')->with('message', 'Marca eliminada del catálogo.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}