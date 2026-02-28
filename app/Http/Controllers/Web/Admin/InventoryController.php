<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura Estricta
use App\DTOs\Admin\Inventory\StockFilterDTO;
use App\Actions\Admin\Inventory\GetConsolidatedStockAction;
use App\Actions\Admin\Inventory\GetPurchaseFormDataAction; // Reutilizamos para el selector de sucursales
use App\Http\Resources\Admin\Inventory\InventoryResource;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request, 
        GetConsolidatedStockAction $action,
        GetPurchaseFormDataAction $formDataAction // Reciclamos esta Action para no duplicar código
    ) {
        // Asumimos que tienes una policy, o usamos el middleware de Spatie
        // $this->authorize('viewAny', InventoryLot::class); 

        $user = auth('super_admin')->user();
        
        $dto = StockFilterDTO::fromRequest($request);
        
        // Inyectamos el branch_id del usuario para cumplir con el Silo de Seguridad
        $stock = $action->execute($dto, $user->branch_id);

        return Inertia::render('Admin/Inventory/Stock/Index', [
            'stock'    => InventoryResource::collection($stock),
            'filters'  => [
                'branch_id' => $dto->branch_id,
                'search'    => $dto->search
            ],
            // Si el admin está anclado a una sucursal, no necesita el selector
            'branches' => $user->branch_id ? [] : $formDataAction->getBranchesForFilter() 
        ]);
    }
}