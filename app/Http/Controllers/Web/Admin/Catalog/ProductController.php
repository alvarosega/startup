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
    ListProductsAction, GetProductStatsAction, GetProductFormDataAction,
    CheckProductExistenceAction
};
use App\Actions\Admin\Catalog\Shared\ReorderEntityAction;
use App\Http\Resources\Admin\Catalog\Product\ProductResource;
use App\Http\Resources\Admin\Catalog\Product\ProductOrderResource;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Inertia\{Inertia, Response as InertiaResponse};
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsAction $listAction, GetProductStatsAction $statsAction): InertiaResponse
    {
        $products = $listAction->execute($request);
        $formData = app(GetProductFormDataAction::class)->execute();

        // Extracción de acoplamiento de modelo estático hacia variable de inyección limpia
        $formData['branches'] = \App\Models\Operations\Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    
        return Inertia::render('Admin/Catalog/Products/Index', [
            'products'   => ProductResource::collection($products),
            'filters'    => $request->only(['search', 'category', 'brand', 'status']),
            'stats'      => $statsAction->execute(),
            'options'    => $formData,
            'can_manage' => true,
        ]);
    }

    public function reorder(Request $request): InertiaResponse
    {
        $products = Product::select(['id', 'name', 'image_path', 'sort_order'])
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('Admin/Catalog/Products//Reorder', [
            'products' => ProductOrderResource::collection($products)
        ]);
    }

    public function updateOrder(Request $request, ReorderEntityAction $action): RedirectResponse
    {
        $request->validate(['ids' => 'required|array']);

        $action->execute('products', $request->ids);

        return redirect()->route('admin.catalog.product.index')
            ->with('success', 'Orden del catálogo global actualizado de forma secuencial.');
    }

    public function checkName(Request $request, CheckProductExistenceAction $action): JsonResponse
    {
        return response()->json(['available' => !$action->execute((string) $request->query('name'))]);
    }

    public function create(GetProductFormDataAction $dataAction): InertiaResponse
    {
        return Inertia::render('Admin/Catalog/Products/Workspace', array_merge(
            $dataAction->execute(),
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

    public function edit(Product $product, GetProductFormDataAction $dataAction): InertiaResponse
    {
        $product->load(['skus.prices', 'brand', 'category']);
        
        return Inertia::render('Admin/Catalog/Products/Workspace', array_merge(
            ['product' => new ProductResource($product)],
            $dataAction->execute()
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