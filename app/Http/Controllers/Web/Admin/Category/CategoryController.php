<?php

namespace App\Http\Controllers\Web\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\{Category, Sku};
use App\DTOs\Admin\Category\CategoryData;
use App\Http\Requests\Admin\Category\{StoreCategoryRequest, UpdateCategoryRequest};
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
    
        $query = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug', 'is_active', 'is_featured', 'sort_order', 'external_code']);
    
        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('external_code', 'like', "%{$search}%"))
                  ->with(['parent:id,name', 'children']);
        } else {
            $query->whereNull('parent_id')->with(['children']);
        }
    
        $categories = CategoryResource::collection(
            $query->orderBy('sort_order')->paginate(20)
        );
    
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'parents'    => Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
            'filters'    => ['search' => $search],
            'can_manage' => $request->user()->can('create', Category::class)
        ]);
    }

    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $action->execute(CategoryData::fromRequest($request));
    
        return redirect()->route('admin.categories.index')->with('success', 'Categoría materializada.');
    }
    
    public function update(UpdateCategoryRequest $request, Category $category, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $category);
        $action->execute(CategoryData::fromRequest($request), $category);
    
        return redirect()->route('admin.categories.index')->with('success', 'Atributos sincronizados.');
    }
    
    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);
        $action->execute($category);
    
        return redirect()->route('admin.categories.index')->with('success', 'Categoría neutralizada.');
    }

    public function updateSkuOrder(Request $request, Category $category, ReorderEntityAction $action): RedirectResponse
    {
        $this->authorize('update', $category);
        $request->validate(['ids' => 'required|array']);
    
        $action->execute('skus', $request->ids);
        return back()->with('success', 'Góndola actualizada.');
    }
    public function skus(Category $category): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $category);

        $skus = Sku::query()
            ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($sku) => [
                'id'           => $sku->id,
                'name'         => $sku->name,
                'product_name' => $sku->product->name,
                'code'         => $sku->code,
                'sort_order'   => $sku->sort_order,
                'image'        => $sku->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($sku->image_path) : null,
            ]);

        return response()->json($skus);
    }
}