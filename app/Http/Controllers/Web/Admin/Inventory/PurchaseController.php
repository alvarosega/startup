<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StorePurchaseRequest;
use App\DTOs\Admin\Inventory\PurchaseDataDTO;
use App\Actions\Admin\Inventory\Purchase\ListPurchasesAction;
use App\Actions\Admin\Inventory\Purchase\GetPurchaseFormOptionsAction;
use App\Actions\Admin\Inventory\Purchase\CreatePurchaseAction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PurchaseController extends Controller
{
    public function index(Request $request, ListPurchasesAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Purchases/Index', [
            'purchases' => $action->execute($request),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(GetPurchaseFormOptionsAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Purchases/Create', $action->execute());
    }

    public function store(StorePurchaseRequest $request, CreatePurchaseAction $action): RedirectResponse
    {
        $dto = PurchaseDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->route('admin.inventory.purchases.index')
            ->with('success', 'Asiento de abastecimiento y capas FIFO materializados con éxito.');
    }
}