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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BrandController extends Controller
{
    use AuthorizesRequests;
    private string $guard = 'super_admin';

    public function index(Request $request, ListBrands $listAction, GetBrandStatsAction $statsAction): Response
    {
        $this->authorize('viewAny', Brand::class);

        return Inertia::render('Admin/Brands/Index', [
            'brands'  => BrandResource::collection($listAction->execute($request->all())),
            'stats'   => $statsAction->execute(),
            'filters' => $request->only(['search', 'provider_id', 'category_id', 'market_zone_id']),
            'options' => $this->getBrandOptions(), // Contrato unificado
            'can_manage' => $request->user($this->guard)->can('create', Brand::class)
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Brand::class);
        return Inertia::render('Admin/Brands/Create', [
            'options' => $this->getBrandOptions()
        ]);
    }

    public function edit(Brand $brand): Response
    {
        $this->authorize('update', $brand);
        $brand->load(['marketZones', 'provider', 'category']);

        return Inertia::render('Admin/Brands/Edit', [
            'brand'   => new BrandResource($brand),
            'options' => $this->getBrandOptions($brand->id)
        ]);
    }

    // --- MÉTODOS DE SOPORTE (LEY DE REUTILIZACIÓN) ---

    private function getBrandOptions(?string $excludeId = null): array
    {
        // Nota: Podrías envolver esto en Cache::remember para optimizar
        return [
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name as name']),
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'zones'      => MarketZone::getMinimalList(),
            'parents'    => Brand::whereNull('parent_id')
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->orderBy('name')
                ->get(['id', 'name'])
        ];
    }

    public function store(StoreBrandRequest $request, UpsertBrandAction $action): RedirectResponse
    {
        $action->execute(BrandData::fromRequest($request));
        return redirect()->route('admin.brands.index')->with('message', 'Protocolo de creación completado.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpsertBrandAction $action): RedirectResponse
    {
        $this->authorize('update', $brand);
        $action->execute(BrandData::fromRequest($request), $brand);
        return redirect()->route('admin.brands.index')->with('message', 'Registro actualizado.');
    }
    public function destroy(Brand $brand, DeleteBrandAction $action): RedirectResponse
    {
        $this->authorize('delete', $brand);
        $action->execute($brand);
        return redirect()->route('admin.brands.index')->with('message', 'Registro neutralizado.');
    }
}