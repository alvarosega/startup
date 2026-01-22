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
    public function index()
    {
        // CORRECCIÓN TÉCNICA:
        // Eliminamos el 'where like search' del backend.
        // Enviamos todo el dataset para que el Frontend pueda construir el Árbol
        // y filtrar dinámicamente sin romper la relación Padre-Hijo.
        
        $categories = Category::with('parent')
            ->orderBy('name', 'asc') // Orden alfabético por defecto
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            // No necesitamos pasar 'filters' porque el filtro es local en Vue
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Create', [
            'parents' => Category::roots()->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('message', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        // CORRECCIÓN DE RUTA: Quitamos 'SuperAdmin' para mantener consistencia
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parents' => Category::roots()
                                 ->where('id', '!=', $category->id) // Evitar auto-referencia cíclica
                                 ->orderBy('name')
                                 ->get(['id', 'name'])
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('message', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: Tiene subcategorías asociadas.']);
        }

        /* if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar: Hay productos asociados.']);
        }
        */

        $category->delete();

        return redirect()->route('admin.categories.index')->with('message', 'Categoría eliminada.');
    }
}