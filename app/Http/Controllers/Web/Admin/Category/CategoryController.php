<?php

namespace App\Http\Controllers\Web\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\{Category, MarketZone};
use App\DTOs\Admin\Category\CategoryData;
use App\Http\Requests\Admin\Category\{StoreCategoryRequest, UpdateCategoryRequest};
use App\Actions\Admin\Category\{UpsertCategoryAction, DeleteCategoryAction};
use App\Http\Resources\CategoryResource;
use Inertia\{Inertia, Response};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    /**
     * Orquestación del árbol de categorías para la administración.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        // "The Law": Lógica de consulta encapsulada en el Modelo.
        $categories = Category::getAllForAdminTree($request->only(['search']));

        return Inertia::render('Admin/Categories/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters'    => $request->only(['search']),
            // Seguridad Zero-Trust: Guard específico para validación de permisos
            'can_manage' => auth('super_admin')->user()->can('create', Category::class)
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Category::class);
    
        return Inertia::render('Admin/Categories/Create', [
            // Enviamos market_zone_id para la lógica de herencia en Vue
            'parents' => Category::roots()->get(['id', 'name', 'market_zone_id']), 
            'zones'   => MarketZone::getMinimalList()
        ]);
    }
    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', Category::class);
        
        // Flujo: Request -> DTO -> Action
        $action->execute(CategoryData::fromRequest($request));

        return redirect()->route('admin.categories.index')
            ->with('success', 'Entidad de categoría persistida correctamente.');
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => new CategoryResource($category->load(['parent', 'marketZone'])),
            'parents'  => Category::getPossibleParents($category->id),
            'zones'    => MarketZone::getMinimalList()
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpsertCategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $category);

        $action->execute(CategoryData::fromRequest($request), $category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Atributos de categoría actualizados.');
    }

    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);

        // Action maneja la integridad (ej: qué pasa con los hijos o productos)
        $action->execute($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría eliminada del registro activo.');
    }
}