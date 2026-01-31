<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Http\Requests\Product\UpsertProductRequest;
use App\DTOs\Product\ProductDTO;
use App\Actions\Product\UpsertProductAction;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request; // Importante para el filtro
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Consulta base
        $query = Product::with([
            'brand', 
            'category',
            // CRÍTICO: Cargar SKUs con TODOS los campos necesarios, incluyendo image_path
            'skus'
        ])->withCount('skus');
    
        // Filtro de búsqueda (Buscamos por Producto, Marca o Código de SKU)
        if ($request->search) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('brand', fn($q2) => $q2->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('skus', fn($q3) => $q3->where('code', 'like', "%{$term}%"));
            });
        }

        $products = $query->latest()->paginate(15)->withQueryString();
    
        return Inertia::render('Admin/Products/Index', [
            'products' => ProductResource::collection($products),
            'filters' => $request->only(['search'])
        ]);
    }

    // ... create, store, edit, update, destroy se mantienen igual ...
    
    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(UpsertProductRequest $request, UpsertProductAction $action)
    {
        $action->execute(ProductDTO::fromRequest($request));
        return redirect()->route('admin.products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        
        // Carga simple (Ya no necesitamos cargar prices para el SKU)
        $product->load(['brand:id,name', 'category:id,name', 'skus']);
        
        // DEBUG: Ver qué estamos cargando
        \Log::info('Product loaded for edit:', [
            'id' => $product->id,
            'name' => $product->name,
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'brand' => $product->brand ? $product->brand->toArray() : null,
            'skus_count' => $product->skus->count(),
            'skus' => $product->skus->map(function($sku) {
                return [
                    'id' => $sku->id,
                    'name' => $sku->name,
                    'code' => $sku->code,
                    // LEEMOS DIRECTO DE LA COLUMNA NUEVA
                    'price' => (float) $sku->base_price,
                ];
            })->toArray(),
        ]);
    
        // Enviar datos planos SIN Resource
        $productArray = [
            'id' => $product->id,
            'name' => $product->name,
            'brand_id' => (string) $product->brand_id, // IMPORTANTE: convertir a string
            'category_id' => $product->category_id,
            'description' => $product->description,
            'image_url' => $product->image_path ? Storage::url($product->image_path) : null,
            'is_active' => (bool) $product->is_active,
            'is_alcoholic' => (bool) $product->is_alcoholic,
            'brand' => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
            'skus' => $product->skus->map(function($sku) {
                return [
                    'id' => $sku->id,
                    'name' => $sku->name,
                    'code' => $sku->code,
                    // CORRECCIÓN: Leemos directo de la nueva columna base_price
                    'price' => (float) $sku->base_price,
                    'conversion_factor' => (float) $sku->conversion_factor,
                    'weight' => (float) $sku->weight,
                    'image_url' => $sku->image_path ? Storage::url($sku->image_path) : null,
                ];
            })->toArray(),
        ];
    
        return Inertia::render('Admin/Products/Edit', [
            'product' => $productArray, // Datos planos en lugar de Resource
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpsertProductRequest $request, Product $product, UpsertProductAction $action)
    {
        $action->execute(ProductDTO::fromRequest($request), $product);
        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado.');
    }
}