<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
// 1. IMPORTANTE: Trait de autorización
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    // 2. USAR EL TRAIT
    use AuthorizesRequests;

    // 3. CORRECCIÓN CLAVE: Inyectar (Request $request)
    public function index(Request $request)
    {
        // Seguridad
        $this->authorize('viewAny', Category::class);

        // Lógica de búsqueda y ordenamiento
        $categories = Category::with('parent')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
            // Permiso para el frontend
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

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }
        
        // Generar slug si no viene (aunque el Request debería validarlo)
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('message', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parents' => Category::roots()
                                 ->where('id', '!=', $category->id)
                                 ->orderBy('name')
                                 ->get(['id', 'name'])
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('message', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: Tiene subcategorías asociadas.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('message', 'Categoría eliminada.');
    }
}