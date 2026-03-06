<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Admin\Product\{StoreProductRequest, UpdateProductRequest};
use App\DTOs\Admin\Product\{CreateProductDTO, UpdateProductDTO};
use App\Actions\Admin\Product\{
    CreateProductAction, UpdateProductAction, DeleteProductAction, 
    ListProductsAction, GetProductStatsAction, GetProductFormDataAction,
    CheckProductExistenceAction
};
use App\Http\Resources\Admin\Product\ProductResource;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Inertia\{Inertia, Response as InertiaResponse};
use Illuminate\Support\Facades\{Log, Auth};

class ProductController extends Controller
{
    private string $guard = 'super_admin'; // Actor específico

    public function index(
        Request $request, 
        ListProductsAction $listAction, 
        GetProductStatsAction $statsAction
    ): InertiaResponse {
        return Inertia::render('Admin/Products/Index', [
            'filters'  => $request->only(['search', 'category', 'brand']),
            'products' => ProductResource::collection($listAction->execute($request)),
            'stats'    => $statsAction->execute(), // Lógica atómica
            'options'  => app(GetProductFormDataAction::class)->execute() // Data para filtros
        ]);
    }

    public function checkName(Request $request, CheckProductExistenceAction $action): JsonResponse
    {
        return response()->json([
            'available' => !$action->execute($request->name)
        ]);
    }

    public function create(GetProductFormDataAction $dataAction): InertiaResponse
    {
        return Inertia::render('Admin/Products/Create', $dataAction->execute(includeChildren: true));
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        Log::info("CATÁLOGO: Creación por " . Auth::guard($this->guard)->id());
        
        $product = $action->execute(CreateProductDTO::fromRequest($request));
        
        return redirect()->route('admin.products.skus.create', $product->id)
            ->with('success', 'Maestro creado.');
    }

    public function edit(Product $product, GetProductFormDataAction $dataAction): InertiaResponse
    {
        return Inertia::render('Admin/Products/Edit', array_merge(
            ['product' => (new ProductResource($product->load(['brand', 'category'])))->resolve()],
            $dataAction->execute(includeChildren: true)
        ));
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): RedirectResponse
    {
        $action->execute($product, UpdateProductDTO::fromRequest($request));
        return redirect()->route('admin.products.index')->with('success', 'Actualizado.');
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        $action->execute($product);
        return redirect()->route('admin.products.index')->with('warning', 'Eliminado.');
    }
}