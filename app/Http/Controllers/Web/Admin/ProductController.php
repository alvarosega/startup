<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Brand, Category};
use App\Http\Requests\Admin\Product\{StoreProductRequest, UpdateProductRequest};
use App\DTOs\Admin\Product\{CreateProductDTO, UpdateProductDTO};
use App\Actions\Admin\Product\{CreateProductAction, UpdateProductAction, DeleteProductAction, ListProductsAction};
use App\Http\Resources\Admin\Product\ProductResource;
use Illuminate\Http\Request;
use Inertia\Response as InertiaResponse;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Lista de productos con filtrado profundo.
     */
    public function index(Request $request, ListProductsAction $listAction): InertiaResponse
    {
        return Inertia::render('Admin/Products/Index', [
            'filters' => $request->only(['search', 'category', 'brand', 'status']),
            'products' => ProductResource::collection($listAction->execute($request)),
            'brands' => Brand::active()->get(['id', 'name']),
            'categories' => Category::whereNull('parent_id')->active()->get(['id', 'name'])
        ]);
    }
    public function checkName(Request $request): \Illuminate\Http\JsonResponse
    {
        $exists = \App\Models\Product::where('name', $request->name)
            ->whereNull('deleted_at')
            ->exists();
    
        return response()->json(['available' => !$exists]);
    }
    /**
     * MÉTODO CORREGIDO: Renderiza el formulario de creación del maestro.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/Products/Create', [
            'brands' => Brand::active()->get(['id', 'name']),
            'categories' => Category::whereNull('parent_id')
                ->with(['children' => fn($q) => $q->active()])
                ->active()
                ->get(['id', 'name'])
        ]);
    }

    /**
     * Persistencia del maestro y redirección al flujo secuencial de SKUs.
     */
    public function store(StoreProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        Log::info("CATÁLOGO: Iniciando creación de producto maestro", [
            'user_id' => auth()->id(),
            'payload' => $request->safe()->except(['image']) // No logueamos binarios
        ]);

        try {
            $product = $action->execute(CreateProductDTO::fromRequest($request));
            
            Log::info("CATÁLOGO: Producto maestro creado con éxito", ['product_id' => $product->id]);

            return redirect()->route('admin.products.skus.create', $product->id)
                ->with('success', 'Maestro creado. Configure variantes.');

        } catch (\Exception $e) {
            Log::error("CATÁLOGO: Fallo en creación de producto", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    /**
     * Edición del maestro.
     */
    public function edit(Product $product): InertiaResponse
    {
        $product->load(['brand', 'category']);
    
        return Inertia::render('Admin/Products/Edit', [
            'product' => (new ProductResource($product))->resolve(),
            'brands' => Brand::active()->get(['id', 'name']),
            'categories' => Category::whereNull('parent_id')
                ->with(['children' => fn($q) => $q->active()])
                ->active()
                ->get(['id', 'name'])
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): RedirectResponse
    {
        Log::notice("CATÁLOGO: Intento de actualización de producto", [
            'product_id' => $product->id,
            'user_id' => auth()->id()
        ]);

        $action->execute($product, UpdateProductDTO::fromRequest($request));

        Log::info("CATÁLOGO: Producto actualizado correctamente", ['product_id' => $product->id]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Catálogo actualizado.');
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        Log::warning("CATÁLOGO: Ejecutando borrado lógico de producto", [
            'product_id' => $product->id,
            'user_id' => auth()->id()
        ]);

        $action->execute($product);

        return redirect()->route('admin.products.index')->with('warning', 'Eliminado.');
    }
}