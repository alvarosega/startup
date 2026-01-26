<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch; // <--- Importar Modelo
use App\Services\ShopContextService;
use App\DTOs\Shop\CatalogQueryDTO;
use App\Actions\Shop\GetShopCatalogAction;
use App\Http\Resources\Shop\ShopProductResource;

class ShopController extends Controller
{
    public function index(
        Request $request, 
        ShopContextService $contextService, 
        GetShopCatalogAction $action
    ) {
        // 1. Obtener ID
        $branchId = $contextService->getActiveBranchId();
        
        // 2. Obtener Nombre para Debug/UI (NUEVO)
        $branchName = Branch::find($branchId)?->name ?? 'Desconocida';

        $dto = CatalogQueryDTO::fromRequest($request, $branchId);
        $paginator = $action->execute($dto);

        $productsResource = ShopProductResource::collection($paginator);

        // Inyectamos contexto
        $productsResource->collection->each(function ($resource) use ($branchId) {
            $resource->setContextBranch($branchId);
        });

        return Inertia::render('Shop/Index', [
            'products' => $productsResource,
            'filters' => $request->only(['search', 'category_id', 'in_stock']),
            'context' => [
                'branch_id' => $branchId,
                'branch_name' => $branchName, // <--- Enviamos el nombre
                'is_fallback' => $branchId === 1 && !session('shop_address_id')
            ]
        ]);
    }
}