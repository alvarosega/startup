<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Http\Requests\Admin\Catalog\Product\StoreProductRequest;
use App\Http\Requests\Admin\Catalog\Product\UpdateProductRequest;
use App\DTOs\Admin\Catalog\Product\ProductData;
use App\Actions\Admin\Catalog\Product\{
    UpsertProductAction, DeleteProductAction, 
    ListProductsAction, GetProductStatsAction, GetProductFormOptionsAction,
    GetProductForEditAction, GetProductsForReorderAction, CheckProductExistenceAction
};
use App\Actions\Admin\Catalog\Shared\ReorderEntityAction;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Inertia\{Inertia, Response as InertiaResponse};
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * RECTIFICACIÓN: Se erradica de forma definitiva el uso de transformadores REST.
     * Toda la hidratación cruzada externa de Branches se delega a GetProductFormOptionsAction.
     */
    public function index(Request $request, ListProductsAction $listAction, GetProductStatsAction $statsAction, GetProductFormOptionsAction $optionsAction): InertiaResponse
    {
        return Inertia::render('Admin/Catalog/Products/Index', [
            'products'   => $listAction->execute($request),
            'filters'    => $request->only(['search', 'category', 'brand', 'status']),
            'stats'      => $statsAction->execute(),
            'options'    => $optionsAction->execute(),
            'can_manage' => true,
        ]);
    }

    /**
     * RECTIFICACIÓN: Lógica extraída quirúrgicamente hacia GetProductsForReorderAction.
     */
    public function reorder(GetProductsForReorderAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Catalog/Products/Reorder', [
            'products' => $action->execute()
        ]);
    }

    public function updateOrder(Request $request, ReorderEntityAction $action): RedirectResponse
    {
        $request->validate(['ids' => 'required|array']);

        $action->execute('products', $request->ids);

        return redirect()->route('admin.catalog.products.index')
            ->with('success', 'Orden del catálogo global actualizado de forma secuencial.');
    }

    public function checkName(Request $request, CheckProductExistenceAction $action): JsonResponse
    {
        return response()->json(['available' => !$action->execute((string) $request->query('name'))]);
    }

    public function create(GetProductFormOptionsAction $optionsAction): InertiaResponse
    {
        return Inertia::render('Admin/Catalog/Products/Workspace', array_merge(
            $optionsAction->execute(),
            [
                'product'  => null,
                'idempKey' => (string) Str::uuid()
            ]
        ));
    }

    public function store(StoreProductRequest $request, UpsertProductAction $action): RedirectResponse
    {
        $product = $action->execute(ProductData::fromRequest($request));
        
        return redirect()->route('admin.catalog.products.edit', $product->id)
            ->with('success', 'Información base materializada. Proceda a configurar variantes físicas y precios.');
    }

    /**
     * RECTIFICACIÓN: Control de transporte de datos plano delegado a GetProductForEditAction.
     */
    public function edit(Product $product, GetProductForEditAction $editAction, GetProductFormOptionsAction $optionsAction): InertiaResponse
    {
        return Inertia::render('Admin/Catalog/Products/Workspace', array_merge(
            ['product' => $editAction->execute($product)],
            $optionsAction->execute()
        ));
    }

    public function update(UpdateProductRequest $request, Product $product, UpsertProductAction $action): RedirectResponse
    {
        $action->execute(ProductData::fromRequest($request), $product);
        
        return redirect()->route('admin.catalog.products.index')->with('success', 'Atributos maestros actualizados.');
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        $action->execute($product);
        
        return redirect()->route('admin.catalog.products.index')->with('success', 'Producto maestro y sus variantes extraídos de circulación.');
    }
}