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

        // RENDIMIENTO EXTREMO: Cache de 24h
        $categories = Cache::remember('admin_categories_list', 86400, function () use ($request) {
            return Category::getAllForAdmin($request->only(['search']));
        });

        return Inertia::render('Admin/Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters'    => $request->only(['search']),
            'can_manage' => auth('super_admin')->user()->can('create', Category::class)
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Category::class);
        return Inertia::render('Admin/Categories/Create'); // Sin 'parents'
    }

    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $action->execute(CategoryData::fromRequest($request));

        return redirect()->route('admin.categories.index')->with('success', 'Categoría operativa.');
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);
        $skus = Sku::query()
        ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
        ->with('product:id,name') // Para contexto visual
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
        return Inertia::render('Admin/Categories/Edit', [
            // CORRECCIÓN: Quitamos el ->load(['parent']) porque la relación ya no existe.
            'category' => new CategoryResource($category) 
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
    public function update(StoreCategoryRequest $request, Category $category, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $category);
        $action->execute(CategoryData::fromRequest($request), $category);

        return redirect()->route('admin.categories.index')->with('success', 'Atributos actualizados.');
    }

    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);
        $action->execute($category);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría neutralizada.');
    }
}