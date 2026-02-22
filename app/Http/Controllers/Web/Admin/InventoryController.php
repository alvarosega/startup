<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Actions\Admin\Inventory\GetConsolidatedInventoryAction;
use App\Actions\Admin\Inventory\RegisterInventoryEntryAction;
use App\DTOs\Admin\Inventory\InventoryFilterDTO;
use App\DTOs\Admin\Inventory\RegisterPurchaseDTO;
use App\DTOs\Admin\Inventory\PurchaseItemDTO;
use App\Http\Requests\Admin\Inventory\RegisterPurchaseRequest;
use App\Http\Resources\Admin\Inventory\InventoryResource; // <--- NUEVO
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request, GetConsolidatedInventoryAction $action): Response
    {
        $filters = new InventoryFilterDTO(
            search: $request->query('search'),
            branch_id: $request->query('branch_id'),
            per_page: 25
        );

        $inventory = $action->execute($filters);

        return Inertia::render('Admin/Inventory/Index', [
            // Usamos el Resource para limpiar la data
            'inventory' => InventoryResource::collection($inventory),
            'branches' => Branch::active()->select('id', 'name')->get(),
            'filters' => [
                'search' => $filters->search,
                'branch_id' => $filters->branch_id
            ]
        ]);
    }

    public function store(RegisterPurchaseRequest $request, RegisterInventoryEntryAction $action)
    {
        $dto = new RegisterPurchaseDTO(
            branch_id:       $request->branch_id,
            provider_id:     $request->provider_id,
            admin_id:        auth('super_admin')->id(), 
            document_number: $request->document_number,
            purchase_date:   $request->purchase_date,
            payment_type:    $request->payment_type,
            total_amount:    $request->total_amount,
            notes:           $request->notes,
            items:           array_map(fn($i) => new PurchaseItemDTO(...$i), $request->items)
        );

        $action->execute($dto);

        return back()->with('success', 'Operación exitosa.');
    }
}