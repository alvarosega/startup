<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Orden: Padres primero, luego sus hijos debajo
        $categories = $query->orderByRaw('COALESCE(parent_id, id), sort_order, id')
            ->get();
            // Nota: Aquí ya no necesitas mapear manualmente si usas 'appends' en el modelo
            // pero para control total en Inertia, un Resource API sería mejor. 
            // Por ahora, pasamos la colección directa, Laravel serializa el JSON con 'image_url'.

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        // Solo enviamos categorías Raíz como posibles padres (Regla 2 niveles)
        return Inertia::render('Admin/Categories/Create', [
            'parents' => Category::roots()->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        // Subida de imagen
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data); // El Observer genera el slug automáticamente

        return redirect()->route('admin.categories.index')->with('message', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/SuperAdmin/Categories/Edit', [
            'category' => $category,
            // Excluimos la categoría actual para que no se elija a sí misma, y solo Roots
            'parents' => Category::roots()
                                 ->where('id', '!=', $category->id)
                                 ->orderBy('name')
                                 ->get(['id', 'name'])
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        // Subida de nueva imagen (El Observer borrará la vieja)
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        // El Observer maneja la cascada de is_active
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('message', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        // Validación: No borrar si tiene hijos
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: Tiene subcategorías asociadas.']);
        }

        // Validación: No borrar si tiene productos (Descomentar cuando tengas Productos)
        /* if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: Hay productos en esta categoría.']);
        }
        */

        $category->delete(); // SoftDelete

        return redirect()->route('admin.categories.index')->with('message', 'Categoría enviada a papelera.');
    }
}