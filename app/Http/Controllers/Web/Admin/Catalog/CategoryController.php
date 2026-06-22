<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Catalog\Sku;
use App\DTOs\Admin\Catalog\Category\CategoryData;
use App\Http\Requests\Admin\Catalog\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Catalog\Category\UpdateCategoryRequest;
use App\Actions\Admin\Catalog\Category\UpsertCategoryAction;
use App\Actions\Admin\Catalog\Category\DeleteCategoryAction;
use App\Actions\Admin\Catalog\Shared\ReorderEntityAction;
use App\Http\Resources\Admin\Catalog\Category\CategoryResource;
use App\Http\Resources\Admin\Catalog\Category\CategorySkuResource;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
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
    
        return Inertia::render('Admin/Catalog/Categories/Index', [
            'categories' => $categories,
            'parents'    => Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
            'filters'    => ['search' => $search],
            'can_manage' => true // Autorización delegada al Middleware HTTP perimetral
        ]);
    }

    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action): RedirectResponse
    {
        $action->execute(CategoryData::fromRequest($request));
    
        return redirect()->route('admin.catalog.categories.index')->with('success', 'Categoría materializada.');
    }
    
    public function update(UpdateCategoryRequest $request, Category $category, UpsertCategoryAction $action): RedirectResponse
    {
        $action->execute(CategoryData::fromRequest($request), $category);
    
        return redirect()->route('admin.catalog.categories.index')->with('success', 'Atributos sincronizados.');
    }
    
    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $action->execute($category);
    
        return redirect()->route('admin.catalog.categories.index')->with('success', 'Categoría neutralizada.');
    }

    public function updateSkuOrder(Request $request, Category $category, ReorderEntityAction $action): RedirectResponse
    {
        $request->validate(['ids' => 'required|array']);
    
        $action->execute('skus', $request->ids);
        return back()->with('success', 'Góndola actualizada.');
    }

    public function skus(Category $category): JsonResponse
    {
        $skus = Sku::query()
            ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->get();

        return response()->json(CategorySkuResource::collection($skus));
    }
}