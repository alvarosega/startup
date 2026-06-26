<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Brand;
use App\Actions\Admin\Catalog\Brand\ListBrandsAction;
use App\Actions\Admin\Catalog\Brand\GetBrandFormOptionsAction;
use App\Actions\Admin\Catalog\Brand\GetBrandStatsAction;
use App\Actions\Admin\Catalog\Brand\UpsertBrandAction;
use App\Actions\Admin\Catalog\Brand\DeleteBrandAction;
use App\DTOs\Admin\Catalog\Brand\BrandData;
use App\Http\Requests\Admin\Catalog\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Catalog\Brand\UpdateBrandRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    /**
     * RECTIFICACIÓN: Controlador depurado de cualquier rastro de consultas SQL directas y lógicas JsonResource.
     */
    public function index(Request $request, ListBrandsAction $listAction, GetBrandStatsAction $statsAction, GetBrandFormOptionsAction $optionsAction): Response
    {
        return Inertia::render('Admin/Catalog/Brands/Index', [
            'brands'     => $listAction->execute($request->all()),
            'stats'      => $statsAction->execute(),
            'filters'    => $request->only(['search', 'provider_id', 'category_id', 'market_zone_id']),
            'options'    => $optionsAction->execute(),
            'can_manage' => true
        ]);
    }

    /**
     * Proporciona la estructura limpia para renderizado perimetral de creación.
     */
    public function create(GetBrandFormOptionsAction $optionsAction): Response
    {
        return Inertia::render('Admin/Catalog/Brands/Create', [
            'options' => $optionsAction->execute()
        ]);
    }

    public function store(StoreBrandRequest $request, UpsertBrandAction $action): RedirectResponse
    {
        $action->execute(BrandData::fromRequest($request));
        
        return redirect()->route('admin.catalog.brands.index')->with('success', 'Marca materializada en el catálogo.');
    }

    /**
     * Proporciona los datos planos mapeados del modelo para hidratación asimilable por Vue en edición.
     */
    public function edit(Brand $brand, GetBrandFormOptionsAction $optionsAction): Response
    {
        $mappedBrand = [
            'id'          => (string) $brand->id,
            'parent_id'   => $brand->parent_id ? (string) $brand->parent_id : null,
            'provider_id' => (string) $brand->provider_id,
            'category_id' => (string) $brand->category_id,
            'name'        => (string) $brand->name,
            'slug'        => (string) $brand->slug,
            'bg_color'    => $brand->bg_color ? (string) $brand->bg_color : null,
            'image_path'  => $brand->image_path ? (string) $brand->image_path : null,
            'website'     => $brand->website ? (string) $brand->website : null,
            'is_active'   => (bool) $brand->is_active,
            'is_featured' => (bool) $brand->is_featured,
            'description' => $brand->description ? (string) $brand->description : null,
        ];

        return Inertia::render('Admin/Catalog/Brands/Edit', [
            'brand'   => $mappedBrand,
            'options' => $optionsAction->execute()
        ]);
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
}