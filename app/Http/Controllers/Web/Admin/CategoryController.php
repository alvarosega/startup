<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\DTOs\Admin\Category\CategoryData;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Actions\Admin\Category\CreateCategory;
use App\Actions\Admin\Category\UpdateCategory;
use App\Actions\Admin\Category\DeleteCategory; // Nueva Action
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        // El modelo encapsula su propia estrategia de obtención de datos
        $categories = Category::getAllForAdminTree();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => CategoryResource::collection($categories)->resolve(),
            'filters'    => $request->only(['search']),
            'can_manage' => auth()->user()->can('create', Category::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Admin/Categories/Create', [
            'parents' => Category::getPossibleParents()
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action)
    {
        $this->authorize('create', Category::class);
        
        $action->execute(CategoryData::fromRequest($request));

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => (new CategoryResource($category))->resolve(),
            'parents'  => Category::getPossibleParents($category->id)
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategory $action)
    {
        $this->authorize('update', $category);

        $action->execute($category, CategoryData::fromRequest($request));

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category, DeleteCategory $action)
    {
        $this->authorize('delete', $category);

        // La Action maneja la validación de integridad y la ejecución atómica
        $action->execute($category);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada.');
    }
}