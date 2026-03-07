<?php

namespace App\Http\Controllers\Web\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\DTOs\Admin\Category\CategoryData;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Actions\Admin\Category\{UpsertCategoryAction, DeleteCategoryAction};
use App\Http\Resources\Admin\Category\CategoryResource;
use Inertia\{Inertia, Response};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\Cache;
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
        return Inertia::render('Admin/Categories/Edit', [
            'category' => new CategoryResource($category)
        ]);
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