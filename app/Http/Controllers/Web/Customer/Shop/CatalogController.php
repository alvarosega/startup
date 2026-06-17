<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContextService;
use App\Actions\Customer\Shop\GetShopCatalogAction;
use App\DTOs\Customer\Shop\CatalogQueryDTO;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        protected readonly ShopContextService $contextService,
        protected readonly GetShopCatalogAction $catalogAction
    ) {}

    /**
     * Renderiza el listado del catálogo comercial bajo el alcance del filtro de contexto activo.
     */
    public function index(Request $request): Response
    {
        // Resuelve el identificador de sucursal (Invitado = Default, Autenticado = Perfil/Sesión)
        $branchId = $this->contextService->getActiveBranchId();

        // Empaquetado del DTO estructurado de consulta
        $dto = new CatalogQueryDTO(
            branchId: $branchId,
            search: $request->input('search'),
            categoryId: $request->input('category_id')
        );

        // Ejecución de la acción filtrada por balances y precios del nodo
        $catalog = $this->catalogAction->execute($dto);

        return Inertia::render('Customer/Shop/Catalog', [
            'catalog' => $catalog,
            'filters' => $request->only(['search', 'category_id'])
        ]);
    }
}