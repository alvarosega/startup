<?php

namespace App\Http\Controllers\Web\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Models\{Brand, Provider, Category, MarketZone};
use App\Actions\Admin\Brand\{ListBrands, UpsertBrandAction, GetBrandStatsAction};
use App\DTOs\Admin\Brand\BrandData;
use App\Http\Requests\Admin\Brand\{StoreBrandRequest, UpdateBrandRequest};
use App\Http\Resources\Admin\Brand\BrandResource;
use Illuminate\Http\{Request, RedirectResponse};
use Inertia\{Inertia, Response};
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BrandController extends Controller
{
    use AuthorizesRequests;
    
    private string $guard = 'super_admin';

    public function index(Request $request, ListBrands $listAction, GetBrandStatsAction $statsAction): Response
    {
        $this->authorize('viewAny', Brand::class);
    
        // LA LEY: Sin caché en el paginador para evitar fallos de serialización de Eloquent
        $brandsPaginator = $listAction->execute($request->all());
    
        return Inertia::render('Admin/Brands/Index', [
            'brands'  => BrandResource::collection($brandsPaginator),
            'stats'   => $statsAction->execute(),
            'filters' => $request->only(['search', 'provider_id', 'category_id', 'market_zone_id']),
            'options' => [
                'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as name']),
                'categories' => Category::active()->whereNull('parent_id')->get(['id', 'name']),
                'zones'      => MarketZone::getMinimalList(),
            ],
            'can_manage' => Auth::guard($this->guard)->user()->can('create', Brand::class)
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Brand::class);
        return Inertia::render('Admin/Brands/Create', [
            // Solo proveedores de la base de datos (verifica si tu campo es company_name o commercial_name)
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as company_name']),
            'categories' => Category::active()->whereNull('parent_id')->get(['id', 'name']),
            'zones'      => MarketZone::getMinimalList()
        ]);
    }

    public function edit(Brand $brand): Response
    {
        $this->authorize('update', $brand);
        return Inertia::render('Admin/Brands/Edit', [
            'brand'      => new BrandResource($brand),
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as company_name']),
            'categories' => Category::active()->whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
            'zones'      => MarketZone::getMinimalList()
        ]);
    }
            
    public function store(StoreBrandRequest $request, UpsertBrandAction $action): RedirectResponse
    {
        $this->authorize('create', Brand::class);
        $action->execute(BrandData::fromRequest($request));
        return redirect()->route('admin.brands.index')->with('message', 'Protocolo de creación de marca completado.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpsertBrandAction $action): RedirectResponse
    {
        $this->authorize('update', $brand);
        $action->execute(BrandData::fromRequest($request), $brand);
        return redirect()->route('admin.brands.index')->with('message', 'Marca actualizada.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);
        $brand->delete(); // Borrado Lógico
        return redirect()->route('admin.brands.index')->with('message', 'Marca eliminada del catálogo.');
    }
}