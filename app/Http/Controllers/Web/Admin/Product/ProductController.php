<?php

declare(strict_types=1);

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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Actions\Admin\Shared\ReorderEntityAction;
use App\Http\Resources\Admin\Product\ProductOrderResource;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AuthorizesRequests;
    
    private string $guard = 'super_admin';

    public function index(Request $request, ListProductsAction $listAction, GetProductStatsAction $statsAction): InertiaResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $products = $listAction->execute($request);
        $formData = app(GetProductFormDataAction::class)->execute();

        $formData['branches'] = \App\Models\Branch::getMinimalList();
    
        return Inertia::render('Admin/Products/Index', [
            'products'   => ProductResource::collection($products),
            'filters'    => $request->only(['search', 'category', 'brand', 'status']),
            'stats'      => $statsAction->execute(),
            'options'    => $formData,
            'can_manage' => $request->user($this->guard)->can('create', Product::class),
        ]);
    }

    public function reorder(Request $request): InertiaResponse
    {
        $this->authorize('update', Product::class);

        $products = Product::select(['id', 'name', 'image_path', 'sort_order'])
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('Admin/Products/Reorder', [
            'products' => ProductOrderResource::collection($products)
        ]);
    }

    public function updateOrder(Request $request, ReorderEntityAction $action): RedirectResponse
    {
        $this->authorize('update', Product::class);
        
        $request->validate(['ids' => 'required|array']);

        // Ejecución directa en base de datos sin lógica de purga de caché
        $action->execute('products', $request->ids);

        return redirect()->route('admin.products.index')
            ->with('success', 'Orden del catálogo global actualizado de forma secuencial.');
    }

    public function checkName(Request $request, CheckProductExistenceAction $action): JsonResponse
    {
        return response()->json(['available' => !$action->execute((string) $request->query('name'))]);
    }

    public function create(GetProductFormDataAction $dataAction): InertiaResponse
    {
        $this->authorize('create', Product::class);
        
        return Inertia::render('Admin/Products/Workspace', array_merge(
            $dataAction->execute(),
            [
                'product'  => null,
                'idempKey' => (string) Str::uuid()
            ]
        ));
    }

    public function store(StoreProductRequest $request, UpsertProductAction $action): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $product = $action->execute(ProductData::fromRequest($request));
        
        // Flujo unificado: Redirecciona al Workspace de edición para desbloquear las pestañas de SKUs y Precios
        return redirect()->route('admin.products.edit', $product->id)
            ->with('success', 'Información base materializada. Proceda a configurar variantes físicas y precios.');
    }

    public function edit(Product $product, GetProductFormDataAction $dataAction): InertiaResponse
    {
        $this->authorize('update', $product);
        
        // Carga atómica de relaciones requeridas para las pestañas secundarias del Workspace
        $product->load(['skus.prices', 'brand', 'category']);
        
        return Inertia::render('Admin/Products/Workspace', array_merge(
            ['product' => new ProductResource($product)],
            $dataAction->execute()
        ));
    }

    public function update(UpdateProductRequest $request, Product $product, UpsertProductAction $action): RedirectResponse
    {
        $this->authorize('update', $product);
        $action->execute(ProductData::fromRequest($request), $product);
        
        return redirect()->route('admin.products.index')->with('success', 'Atributos maestros actualizados.');
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        $this->authorize('delete', $product);
        $action->execute($product);
        
        return redirect()->route('admin.products.index')->with('success', 'Producto maestro y sus variantes extraídos de circulación.');
    }
}