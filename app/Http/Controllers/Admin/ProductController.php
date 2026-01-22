<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Price;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {

        $this->authorize('viewAny', Product::class);
        // CAMBIO CRÍTICO: Agregamos 'skus' al eager loading
        // También traemos 'skus.prices' si quisieras mostrar precios (opcional por ahora)
        $query = Product::with(['brand', 'category', 'skus']) 
            ->withCount('skus'); 
    
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhereHas('brand', function($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
    
        $products = $query->orderBy('name', 'asc') // Orden alfabético es mejor para listas largas
            ->paginate(15)
            ->withQueryString();
    
        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search']),
            'can_manage' => auth()->user()->can('create', Product::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        return Inertia::render('Admin/Products/Create', [
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name', 'provider_id']),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);
        // La validación ya pasó aquí gracias al Request
        $data = $request->validated();

        try {
            DB::transaction(function () use ($request, $data) {
                
                // 1. Guardar Imagen
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('products', 'public');
                }

                // 2. Crear Producto
                $product = Product::create([
                    'name' => $data['name'],
                    'brand_id' => $data['brand_id'],
                    'category_id' => $data['category_id'],
                    'description' => $data['description'],
                    'image_path' => $imagePath,
                    'is_active' => true
                ]);

                // 3. Crear SKUs y Precios
                foreach ($data['skus'] as $skuData) {
                    $sku = Sku::create([
                        'product_id' => $product->id,
                        'name' => $skuData['name'],
                        'code' => $skuData['code'],
                        'conversion_factor' => $skuData['conversion_factor'],
                        'weight' => $skuData['weight'] ?? 0,
                    ]);

                    // Precio Base Inicial
                    Price::create([
                        'sku_id' => $sku->id,
                        'branch_id' => null, // null = Precio Nacional
                        'list_price' => $skuData['price'], // Simplificado para el MVP
                        'final_price' => $skuData['price'],
                        'min_quantity' => 1,
                        'valid_from' => now()
                    ]);
                }
            });

            return redirect()->route('admin.products.index')->with('message', 'Producto y SKUs creados exitosamente.');

        } catch (\Exception $e) {
            // Manejo de errores de BD inesperados
            return back()->withErrors(['error' => 'Error de Sistema: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        // Traemos el producto con sus SKUs y el PRECIO VIGENTE de cada SKU
        $product = Product::with(['skus' => function($q) {
            $q->with(['prices' => function($p) {
                $p->orderBy('id', 'desc')->limit(1); // Último precio
            }]);
        }])->findOrFail($id);
        $this->authorize('update', $product);
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }
    
    public function update(UpdateProductRequest $request, $id)
    {

        $product = Product::findOrFail($id);
        $this->authorize('update', $product);
        $data = $request->validated();

        try {
            DB::transaction(function () use ($product, $data, $request) {
                
                // --- PASO 1: Actualizar Padre (Producto) ---
                $productData = [
                    'name' => $data['name'],
                    'brand_id' => $data['brand_id'],
                    'category_id' => $data['category_id'],
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                    'is_alcoholic' => $data['is_alcoholic'],
                ];

                // El Observer se encarga de borrar la imagen vieja si suben una nueva
                if ($request->hasFile('image')) {
                    $productData['image_path'] = $request->file('image')->store('products', 'public');
                }

                $product->update($productData);

                // --- PASO 2: Sincronización de SKUs ---
                
                // A. Identificar IDs que vinieron en el formulario (Los que deben sobrevivir)
                $incomingSkuIds = collect($data['skus'])
                    ->pluck('id')
                    ->filter() // Quita nulos (los nuevos)
                    ->toArray();

                // B. ELIMINAR (SoftDelete) los que estaban en BD pero NO vinieron en el formulario
                // "Si tienes un SKU en base de datos que no está en la lista entrante, bórralo"
                $product->skus()->whereNotIn('id', $incomingSkuIds)->delete();

                // C. CREAR o ACTUALIZAR (Upsert Lógico)
                foreach ($data['skus'] as $skuData) {
                    
                    if (isset($skuData['id']) && $skuData['id']) {
                        // ACTUALIZAR EXISTENTE
                        $sku = Sku::find($skuData['id']);
                        $sku->update([
                            'name' => $skuData['name'],
                            'code' => $skuData['code'],
                            'conversion_factor' => $skuData['conversion_factor'],
                            'weight' => $skuData['weight'] ?? 0,
                        ]);
                    } else {
                        // CREAR NUEVO (Porque no trajo ID)
                        $sku = $product->skus()->create([
                            'name' => $skuData['name'],
                            'code' => $skuData['code'],
                            'conversion_factor' => $skuData['conversion_factor'],
                            'weight' => $skuData['weight'] ?? 0,
                            'is_active' => true
                        ]);
                    }

                    // Actualizar Precio (Usando el helper del modelo)
                    $sku->updatePrice($skuData['price']);
                }
            });

            return redirect()->route('admin.products.index')->with('message', 'Producto actualizado correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
    // 5. AGREGAR MÉTODO DESTROY (Faltaba en tu código original)
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // SEGURIDAD
        $this->authorize('delete', $product);

        // Opcional: Validar si tiene stock o ventas históricas antes de borrar
        $product->delete(); // SoftDelete

        return redirect()->route('admin.products.index')->with('message', 'Producto eliminado.');
    }
}