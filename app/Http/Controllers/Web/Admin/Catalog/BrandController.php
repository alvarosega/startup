<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Brand;
use App\Models\Provider;
use App\Models\Catalog\Category;
use App\Actions\Admin\Catalog\Brand\{ListBrands, UpsertBrandAction, GetBrandStatsAction, DeleteBrandAction};
use App\DTOs\Admin\Catalog\Brand\BrandData;
use App\Http\Requests\Admin\Catalog\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Catalog\Brand\UpdateBrandRequest;
use App\Http\Resources\Admin\Catalog\Brand\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(Request $request, ListBrands $listAction, GetBrandStatsAction $statsAction): Response
    {
        return Inertia::render('Admin/Catalog/Brands/Index', [
            'brands'     => BrandResource::collection($listAction->execute($request->all())),
            'stats'      => $statsAction->execute(),
            'filters'    => $request->only(['search', 'provider_id', 'category_id', 'market_zone_id']),
            'options'    => $this->getBrandOptions(),
            'can_manage' => true
        ]);
    }

    public function store(StoreBrandRequest $request, UpsertBrandAction $action): RedirectResponse
    {
        $action->execute(BrandData::fromRequest($request));
        
        return redirect()->route('admin.catalog.brands.index')->with('success', 'Marca materializada en el catálogo.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UpsertBrandAction $action): RedirectResponse
    {
        $action->execute(BrandData::fromRequest($request), $brand);
        
        return redirect()->route('admin.catalog.brands.index')->with('success', 'Atributos de marca sincronizados.');
    }

    public function destroy(Brand $brand, DeleteBrandAction $action): RedirectResponse
    {
        $action->execute($brand);
        
        return redirect()->route('admin.catalog.brands.index')->with('success', 'Marca neutralizada del sistema.');
    }

    private function getBrandOptions(): array
    {
        return [
            'providers'  => Provider::where('is_active', true)->orderBy('commercial_name')->get(['id', 'commercial_name as name']),
            'categories' => Category::where('is_active', true)->whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
            'parents'    => Brand::whereNull('parent_id')->orderBy('name')->get(['id', 'name'])
        ];
    }
}