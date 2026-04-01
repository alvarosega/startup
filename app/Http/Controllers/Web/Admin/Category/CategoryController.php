<?php

namespace App\Http\Controllers\Web\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\{Category, Sku};
use App\DTOs\Admin\Category\CategoryData;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Actions\Admin\Category\{UpsertCategoryAction, DeleteCategoryAction};
use App\Actions\Admin\Shared\ReorderEntityAction;
use App\Http\Resources\Admin\Category\CategoryResource;
use Inertia\{Inertia, Response};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Cache, Storage};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);
    
        $search = (string) $request->search;
        // REGLA 3.A: Llave atada a versión global para evitar flush()
        $version = Cache::get('admin_categories_version', 1);
        $cacheKey = "admin_cat_v{$version}_s" . md5($search . $request->cursor);
    
        $categories = Cache::remember($cacheKey, 3600, fn() => 
            CategoryResource::collection(
                Category::query()
                    ->select(['id', 'parent_id', 'name', 'slug', 'is_active', 'is_featured', 'sort_order', 'external_code', 'version'])
                    ->with(['parent:id,name'])
                    ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('external_code', 'like', "%{$search}%"))
                    ->orderBy('sort_order')
                    ->cursorPaginate(20)
            )
        );
    
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters'    => ['search' => $search],
            'can_manage' => $request->user()->can('create', Category::class)
        ]);
    }
    public function create(): Response
    {
        $this->authorize('create', Category::class);
        
        return Inertia::render('Admin/Categories/Create', [
            // Jerarquía: Permitimos cualquier categoría como padre (excepto en edit)
            'parents' => Category::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', Category::class);
        
        $action->execute(CategoryData::fromRequest($request));

        // Limpiar toda la caché del catálogo para que el nuevo nodo aparezca
        Cache::increment('admin_categories_version');
        return redirect()->route('admin.categories.index')->with('success', 'Categoría materializada en el sistema.');
    }
    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);
        
        // Carga diferida de relaciones necesarias para el form
        $category->load(['parent']);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => new CategoryResource($category),
            // PROTOCOLO: Lista de potenciales padres excluyendo el nodo actual
            'parents'  => Category::where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(['id', 'name'])
        ]);
    }
    
    public function skuOrder(Category $category): Response
    {
        $this->authorize('update', $category);

        $skus = Sku::query()
            ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($sku) => [
                'id' => $sku->id,
                'name' => $sku->name,
                'product_name' => $sku->product->name,
                'code' => $sku->code,
                'sort_order' => $sku->sort_order,
                'image' => $sku->image_path ? Storage::disk('public')->url($sku->image_path) : null,
            ]);

        return Inertia::render('Admin/Categories/SkuOrder/Index', [
            'category' => new CategoryResource($category),
            'skus' => $skus
        ]);
    }

    public function updateSkuOrder(Request $request, Category $category, ReorderEntityAction $action): RedirectResponse
    {
        $this->authorize('update', $category);
        $request->validate(['ids' => 'required|array']);
    
        $action->execute('skus', $request->ids);
        
        // PILAR 1.E: INVALIDACIÓN SELECTIVA
        // Al reordenar SKUs, no necesitamos borrar el navegador de categorías, 
        // pero si cachearas los productos, aquí deberías borrar esa llave.
        
        return back()->with('success', 'Góndola actualizada.');
    }
    public function update(UpdateCategoryRequest $request, Category $category, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $category);
        $action->execute(CategoryData::fromRequest($request), $category);

        // INVALIDACIÓN SELECTIVA: Solo borramos el catálogo de categorías
        // En lugar de flush(), usamos la llave específica o borrado por prefijo
        Cache::increment('admin_categories_version');
        // Nota: Para borrar los MD5 se requiere un iterador de archivos o un Version Hash.
        // Por ahora, flush() es aceptable solo si no hay otros datos críticos en caché.

        return redirect()->route('admin.categories.index')->with('success', 'Atributos sincronizados.');
    }
    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);
        $action->execute($category);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría neutralizada.');
    }
}