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
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Transforma y sirve datos estructurados planos optimizando la rehidratación del DOM en Vue/Inertia sin JsonResources.
     */
    public function index(Request $request): Response
    {
        $search = $request->filled('search') ? (string) $request->search : null;
    
        $query = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug', 'is_active', 'is_featured', 'sort_order', 'external_code']);
    
        if ($search) {
            $term = "%{$search}%";
            $query->where(fn($q) => $q->where('name', 'like', $term)->orWhere('external_code', 'like', $term))
                  ->with(['parent:id,name', 'children']);
        } else {
            $query->whereNull('parent_id')->with(['children']);
        }
    
        $paginator = $query->orderBy('sort_order')->paginate(20);

        // Mapeo nativo plano absoluto para erradicar el anidamiento estructural
        $mappedCategories = array_map(function ($category) {
            return [
                'id'            => (string) $category->id,
                'parent_id'     => $category->parent_id ? (string) $category->parent_id : null,
                'name'          => (string) $category->name,
                'slug'          => (string) $category->slug,
                'is_active'     => (bool) $category->is_active,
                'is_featured'   => (bool) $category->is_featured,
                'sort_order'    => (int) $category->sort_order,
                'external_code' => $category->external_code ? (string) $category->external_code : null,
                'parent'        => $category->parent ? [
                    'id'   => (string) $category->parent->id,
                    'name' => (string) $category->parent->name
                ] : null,
                'children'      => $category->children->map(fn($child) => [
                    'id'            => (string) $child->id,
                    'parent_id'     => (string) $child->parent_id,
                    'name'          => (string) $child->name,
                    'slug'          => (string) $child->slug,
                    'is_active'     => (bool) $child->is_active,
                    'is_featured'   => (bool) $child->is_featured,
                    'sort_order'    => (int) $child->sort_order,
                    'external_code' => $child->external_code ? (string) $child->external_code : null,
                ])->toArray()
            ];
        }, $paginator->items());

        $categoriesStructure = [
            'data' => $mappedCategories,
            'links' => [
                'next' => $paginator->nextPageUrl(),
                'prev' => $paginator->previousPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ]
        ];
    
        $parents = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => ['id' => (string) $p->id, 'name' => (string) $p->name])
            ->toArray();

        return Inertia::render('Admin/Catalog/Categories/Index', [
            'categories' => $categoriesStructure,
            'parents'    => $parents,
            'filters'    => ['search' => $search],
            'can_manage' => true
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
        $request->validate(['ids' => 'required|array', 'ids.*' => 'required|string']);
    
        $action->execute('skus', $request->ids);
        return back()->with('success', 'Góndola actualizada.');
    }

    /**
     * Retorna arreglos puros estructurados para componentes dinámicos asíncronos en el frontend.
     */
    public function skus(Category $category): JsonResponse
    {
        $skus = Sku::query()
            ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
            ->with('product:id,name')
            ->orderBy('sort_order')
            ->get();

        $mappedSkus = $skus->map(fn($sku) => [
            'id'         => (string) $sku->id,
            'name'       => (string) ($sku->name ?? $sku->product?->name),
            'sort_order' => (int) $sku->sort_order,
            'product'    => $sku->product ? [
                'id'   => (string) $sku->product->id,
                'name' => (string) $sku->product->name
            ] : null
        ])->toArray();

        return response()->json($mappedSkus);
    }
}