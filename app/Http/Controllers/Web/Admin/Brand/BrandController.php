<?php

namespace App\Http\Controllers\Web\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Models\{Brand, Provider, Category};
use App\Actions\Admin\Brand\{ListBrands, UpsertBrandAction, GetBrandStatsAction, DeleteBrandAction};
use App\DTOs\Admin\Brand\BrandData;
use App\Http\Requests\Admin\Brand\{StoreBrandRequest, UpdateBrandRequest};
use App\Http\Resources\Admin\Brand\BrandResource;
use Illuminate\Http\{Request, RedirectResponse};
use Inertia\{Inertia, Response};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BrandController extends Controller
{
    use AuthorizesRequests;
    private string $guard = 'super_admin';

    public function index(Request $request, ListBrands $listAction, GetBrandStatsAction $statsAction): Response
    {
        $this->authorize('viewAny', Brand::class);

        return Inertia::render('Admin/Brands/Index', [
            'brands'     => BrandResource::collection($listAction->execute($request->all())),
            'stats'      => $statsAction->execute(),
            'filters'    => $request->only(['search', 'provider_id', 'category_id']),
            'options'    => $this->getBrandOptions(),
            'can_manage' => $request->user($this->guard)->can('create', Brand::class)
        ]);
    }

    public function store(StoreBrandRequest $request, UpsertBrandAction $action): RedirectResponse
    {
        $this->authorize('create', Brand::class);
        $action->execute(BrandData::fromRequest($request));
        
        return redirect()->route('admin.brands.index')->with('success', 'Marca materializada en el catálogo.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpsertBrandAction $action): RedirectResponse
    {
        $this->authorize('update', $brand);
        $action->execute(BrandData::fromRequest($request), $brand);
        
        return redirect()->route('admin.brands.index')->with('success', 'Atributos de marca sincronizados.');
    }

    public function destroy(Brand $brand, DeleteBrandAction $action): RedirectResponse
    {
        $this->authorize('delete', $brand);
        $action->execute($brand);
        
        return redirect()->route('admin.brands.index')->with('success', 'Marca neutralizada del sistema.');
    }

    private function getBrandOptions(): array
    {
        return [
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as name']),
            'categories' => Category::active()->whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
            'parents'    => Brand::whereNull('parent_id')->orderBy('name')->get(['id', 'name'])
        ];
    }
}