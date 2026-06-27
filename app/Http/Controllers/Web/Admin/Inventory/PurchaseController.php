<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Purchase\StorePurchaseRequest;
use App\Http\Requests\Admin\Inventory\Purchase\CompletePurchaseRequest;
use App\DTOs\Admin\Inventory\Purchase\PurchaseDataDTO;
use App\DTOs\Admin\Inventory\Purchase\CompletePurchaseDataDTO;
use App\Actions\Admin\Inventory\Purchase\ListPurchasesAction;
use App\Actions\Admin\Inventory\Purchase\GetPurchaseFormOptionsAction;
use App\Actions\Admin\Inventory\Purchase\GetPurchaseEditDataAction;
use App\Actions\Admin\Inventory\Purchase\CreatePurchaseAction;
use App\Actions\Admin\Inventory\Purchase\CompletePendingPurchaseAction;
use App\Actions\Admin\Inventory\Purchase\CancelPendingPurchaseAction;
use App\Models\Inventory\Purchase;
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

        return redirect()->route('admin.purchases.index')
            ->with('success', 'El asiento de abastecimiento inicial ha sido procesado de forma correcta.');
    }

    /**
     * RECTIFICACIÓN: Incorporación de método plano para renderizar el formulario independiente de ingreso físico FIFO
     */
    public function edit(Purchase $purchase, GetPurchaseEditDataAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Purchases/Edit', $action->execute($purchase));
    }

    public function complete(Purchase $purchase, CompletePurchaseRequest $request, CompletePendingPurchaseAction $action): RedirectResponse
    {
        $dto = CompletePurchaseDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($purchase, $dto, $adminId);

        return redirect()->route('admin.purchases.index')
            ->with('success', 'La compra pendiente ha sido consolidada. Existencias físicas e historial FIFO inyectados.');
    }

    public function cancel(Purchase $purchase, CancelPendingPurchaseAction $action): RedirectResponse
    {
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($purchase, $adminId);

        return redirect()->route('admin.purchases.index')
            ->with('success', 'Orden de compra cancelada correctamente y purgada de los flujos de trabajo activos.');
    }
}