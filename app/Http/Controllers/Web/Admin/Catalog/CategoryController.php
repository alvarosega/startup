<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\DTOs\Admin\Catalog\Category\CategoryData;
use App\Http\Requests\Admin\Catalog\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Catalog\Category\UpdateCategoryRequest;
use App\Actions\Admin\Catalog\Category\ListCategoriesAction;
use App\Actions\Admin\Catalog\Category\GetCategoryParentsAction;
use App\Actions\Admin\Catalog\Category\GetCategoryForEditAction;
use App\Actions\Admin\Catalog\Category\UpsertCategoryAction;
use App\Actions\Admin\Catalog\Category\DeleteCategoryAction;
use App\Actions\Admin\Catalog\Shared\ReorderEntityAction;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    /**
     * Orquestador delgado puro. Delega el listado y la evaluación diferida de SKUs a la acción.
     */
    public function index(Request $request, ListCategoriesAction $action): Response
    {
        return Inertia::render('Admin/Catalog/Categories/Index', $action->execute($request->all()));
    }

    /**
     * Orquestador puro para la renderización de la vista de creación.
     */
    public function create(GetCategoryParentsAction $action): Response
    {
        return Inertia::render('Admin/Catalog/Categories/Create', [
            'parents' => $action->execute()
        ]);
    }

    /**
     * Procesa la creación delegando la persistencia y el procesamiento multimedia.
     */
    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action): RedirectResponse
    {
        $action->execute(CategoryData::fromRequest($request));
    
        return redirect()->route('admin.catalog.categories.index')->with('success', 'Categoría materializada.');
    }

    /**
     * Orquestador puro para la renderización de la vista de edición.
     */
    public function edit(Category $category, GetCategoryForEditAction $action): Response
    {
        return Inertia::render('Admin/Catalog/Categories/Edit', $action->execute($category));
    }
    
    /**
     * Procesa la actualización delegando las integridades de nivel y jerarquía a la acción.
     */
    public function update(UpdateCategoryRequest $request, Category $category, UpsertCategoryAction $action): RedirectResponse
    {
        $action->execute(CategoryData::fromRequest($request), $category);
    
        return redirect()->route('admin.catalog.categories.index')->with('success', 'Atributos sincronizados.');
    }
    
    /**
     * Procesa el borrado lógico aislando la verificación de llaves foráneas.
     */
    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $action->execute($category);
    
        return redirect()->route('admin.catalog.categories.index')->with('success', 'Categoría neutralizada.');
    }

    /**
     * Modifica el sort_order secuencial dentro de una transacción aislada.
     */
    public function updateSkuOrder(Request $request, Category $category, ReorderEntityAction $action): RedirectResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'required|string']);
    
        $action->execute('skus', $request->ids);
        return back()->with('success', 'Góndola actualizada.');
    }
}