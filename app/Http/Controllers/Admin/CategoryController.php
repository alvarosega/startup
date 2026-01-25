<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura Clean
use App\DTOs\Category\CategoryData;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Actions\Category\CreateCategory;
use App\Actions\Category\UpdateCategory;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        // Obtenemos todas las categorías para que el frontend arme el árbol
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Admin/Categories/Index', [
            // .resolve() es importante para evitar el envoltorio 'data'
            'categories' => CategoryResource::collection($categories)->resolve(),
            'filters' => $request->only(['search']),
            'can_manage' => auth()->user()->can('create', Category::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Admin/Categories/Create', [
            'parents' => Category::roots()->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action)
    {
        $this->authorize('create', Category::class);
        
        $data = CategoryData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => (new CategoryResource($category))->resolve(),
            'parents' => Category::roots()
                        ->where('id', '!=', $category->id)
                        ->orderBy('name')
                        ->get(['id', 'name'])
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategory $action)
    {
        $this->authorize('update', $category);

        $data = CategoryData::fromRequest($request);
        $action->execute($category, $data);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: tiene subcategorías.']);
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada.');
    }
}