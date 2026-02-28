<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase; // Solo se importa para el $this->authorize()
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;

// Arquitectura Estricta
use App\Http\Requests\Admin\Inventory\RegisterPurchaseRequest;
use App\DTOs\Admin\Inventory\{RegisterPurchaseDTO, PurchaseFilterDTO};
use App\Actions\Admin\Inventory\{
    RegisterInventoryEntryAction, 
    GetPurchasesAction, 
    GetPurchaseFormDataAction
};
use App\Http\Resources\Admin\Inventory\PurchaseResource;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request, 
        GetPurchasesAction $listAction, 
        GetPurchaseFormDataAction $formDataAction
    ) {
        $this->authorize('viewAny', Purchase::class);

        $dto = PurchaseFilterDTO::fromRequest($request);
        $purchases = $listAction->execute($dto);

        return Inertia::render('Admin/Inventory/Purchases/Index', [
            'purchases' => PurchaseResource::collection($purchases),
            'branches'  => $formDataAction->getBranchesForFilter(), // Delegado
            'filters'   => [
                'branch_id' => $dto->branch_id,
                'type'      => $dto->type
            ]
        ]);
    }

    public function create(GetPurchaseFormDataAction $action)
    {
        $this->authorize('create', Purchase::class);
        
        // Guard explícito según las reglas
        $user = auth('super_admin')->user(); 

        // Orquestación pura: Pasamos el parámetro de seguridad a la Action
        $formData = $action->execute($user->branch_id);

        return Inertia::render('Admin/Inventory/Purchases/Create', $formData);
    }

    public function store(RegisterPurchaseRequest $request, RegisterInventoryEntryAction $action)
    {
        $this->authorize('create', Purchase::class);
        
        try {
            $dto = RegisterPurchaseDTO::fromRequest($request);
            $action->execute($dto);

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Inventario ingresado y contabilizado correctamente.');
                
        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al procesar el ingreso: ' . $e->getMessage()])->withInput();
        }
    }
}