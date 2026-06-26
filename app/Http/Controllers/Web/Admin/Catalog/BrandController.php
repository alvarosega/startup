<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Brand;
use App\Actions\Admin\Catalog\Brand\ListBrandsAction;
use App\Actions\Admin\Catalog\Brand\GetBrandFormOptionsAction;
use App\Actions\Admin\Catalog\Brand\GetBrandStatsAction;
use App\Actions\Admin\Catalog\Brand\GetBrandForEditAction;
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
     * RECTIFICACIÓN: Se elimina por completo la inyección redundante de 'options' en el índice general,
     * liberando procesamiento en la base de datos de consultas no solicitadas en el listado.
     */
    public function index(Request $request, ListBrandsAction $listAction, GetBrandStatsAction $statsAction): Response
    {
        return Inertia::render('Admin/Catalog/Brands/Index', [
            'brands'     => $listAction->execute($request->all()),
            'stats'      => $statsAction->execute(),
            'filters'    => $request->only(['search', 'provider_id', 'category_id', 'market_zone_id']),
            'can_manage' => true
        ]);
    }

    /**
     * Sirve las dependencias mapeadas exclusivamente al instanciar interfaces de creación.
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
     * RECTIFICACIÓN: El controlador actúa como despachador puro delegando la deconstrucción a GetBrandForEditAction.
     */
    public function edit(Brand $brand, GetBrandForEditAction $brandAction, GetBrandFormOptionsAction $optionsAction): Response
    {
        return Inertia::render('Admin/Catalog/Brands/Edit', [
            'brand'   => $brandAction->execute($brand),
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