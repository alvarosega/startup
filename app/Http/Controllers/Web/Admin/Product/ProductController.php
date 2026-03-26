<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Admin\Product\{StoreProductRequest, UpdateProductRequest};
use App\DTOs\Admin\Product\ProductData;
use App\Actions\Admin\Product\{
    UpsertProductAction, DeleteProductAction, 
    ListProductsAction, GetProductStatsAction, GetProductFormDataAction,
    CheckProductExistenceAction
};
use App\Http\Resources\Admin\Product\ProductResource;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Inertia\{Inertia, Response as InertiaResponse};
use Illuminate\Support\Facades\{Cache, Auth};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    private string $guard = 'super_admin';
    public function index(Request $request, ListProductsAction $listAction, GetProductStatsAction $statsAction)
    {
        $this->authorize('viewAny', Product::class);
        $products = $listAction->execute($request);
    
        return Inertia::render('Admin/Products/Index', [
            'products'   => ProductResource::collection($products),
            'filters'    => $request->only(['search', 'category', 'brand', 'status']),
            'stats'      => $statsAction->execute(),
            'options'    => app(GetProductFormDataAction::class)->execute(),
            'can_manage' => auth()->user()->can('create', Product::class),
        ]);
    }

    public function checkName(Request $request, CheckProductExistenceAction $action): JsonResponse
    {
        return response()->json(['available' => !$action->execute($request->query('name'))]);
    }

    public function create(GetProductFormDataAction $dataAction): InertiaResponse
    {
        $this->authorize('create', Product::class);
        
        // LA LEY: Pre-generación de llave de idempotencia
        return Inertia::render('Admin/Products/Create', array_merge(
            $dataAction->execute(),
            ['idempKey' => (string) \Illuminate\Support\Str::uuid7()]
        ));
    }
    public function store(StoreProductRequest $request, UpsertProductAction $action): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $product = $action->execute(ProductData::fromRequest($request));
        
        return redirect()->route('admin.products.skus.create', $product->id)
            ->with('success', 'Maestro creado. Proceda a configurar variantes.');
    }

    public function edit(Product $product, GetProductFormDataAction $dataAction): InertiaResponse
    {
        $this->authorize('update', $product);
        
        return Inertia::render('Admin/Products/Edit', array_merge(
            ['product' => new ProductResource($product)],
            $dataAction->execute()
        ));
    }

    public function update(UpdateProductRequest $request, Product $product, UpsertProductAction $action): RedirectResponse
    {
        $this->authorize('update', $product);
        $action->execute(ProductData::fromRequest($request), $product);
        return redirect()->route('admin.products.index')->with('success', 'Catálogo actualizado.');
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        $this->authorize('delete', $product);
        $action->execute($product);
        return redirect()->route('admin.products.index')->with('warning', 'Maestro eliminado.');
    }
}