<?php

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;

/** * ARQUITECTURA DE SILO: PURCHASES 
 * Todas las dependencias han sido relocalizadas al sub-dominio /Purchase/
 */
use App\Http\Requests\Admin\Inventory\Purchase\RegisterPurchaseRequest;
use App\DTOs\Admin\Inventory\Purchase\{RegisterPurchaseDTO, PurchaseFilterDTO};
use App\Actions\Admin\Inventory\Purchase\{
    RegisterInventoryEntryAction, 
    GetPurchasesAction, 
    GetPurchaseFormDataAction
};
use App\Http\Resources\Admin\Inventory\PurchaseResource;
use App\Http\Resources\Admin\Inventory\PurchaseLotResource;

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
            'branches'  => $formDataAction->getBranchesForFilter(),
            'filters'   => [
                'branch_id' => $dto->branch_id,
                'type'      => $dto->type
            ]
        ]);
    }

    public function create(GetPurchaseFormDataAction $action)
    {
        $this->authorize('create', Purchase::class);
        $user = auth('super_admin')->user(); 

        // Inyección de silo basada en la sucursal del Admin
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
                ->with('success', 'Inventario contabilizado y sincronizado con éxito.');
                
        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'FALLO_ATÓMICO: ' . $e->getMessage()])->withInput();
        }
    }

    public function items(string $purchaseId)
    {
        $purchase = Purchase::with([
            'inventoryLots.sku:id,product_id,name,code',
            'inventoryLots.sku.product:id,name'
        ])->findOrFail($purchaseId);
    
        $this->authorize('view', $purchase);
    
        return PurchaseLotResource::collection($purchase->inventoryLots);
    }
}