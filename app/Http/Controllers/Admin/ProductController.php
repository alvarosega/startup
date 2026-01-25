<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura
use App\DTOs\Product\ProductData;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Actions\Product\CreateProduct;
use App\Actions\Product\UpdateProduct;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with([
            'brand', 
            'category', 
            'skus.prices' => fn($q) => $q->whereNull('branch_id')->latest()
        ])->withCount('skus');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhereHas('brand', function($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        // 1. Obtenemos el Paginator original
        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        // 2. Transformamos solo los items usando el Resource
        $transformedData = ProductResource::collection($products)->resolve();

        return Inertia::render('Admin/Products/Index', [
            // 3. Construimos manualmente la respuesta para mantener 'data' y 'links' al nivel raíz
            // Esto es necesario para que tu <v-for="link in products.links"> funcione
            'products' => [
                'data' => $transformedData,
                'links' => $products->linkCollection()->toArray(), 
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'filters' => $request->only(['search']),
            'can_manage' => auth()->user()->can('create', Product::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        return Inertia::render('Admin/Products/Create', [
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreProductRequest $request, CreateProduct $action)
    {
        $this->authorize('create', Product::class);
        
        $data = ProductData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.products.index')->with('success', 'Producto creado.');
    }

    public function edit(Product $product) // Route Model Binding inyecta el modelo
    {
        $this->authorize('update', $product);
        
        // Cargar relaciones necesarias para el formulario
        $product->load([
            'skus.prices' => fn($q) => $q->whereNull('branch_id')->orderBy('id', 'desc')
        ]);

        return Inertia::render('Admin/Products/Edit', [
            'product' => (new ProductResource($product))->resolve(),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $action)
    {
        $this->authorize('update', $product);

        $data = ProductData::fromRequest($request);
        $action->execute($product, $data);

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado.');
    }
}