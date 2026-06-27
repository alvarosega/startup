<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\TransferSafetyRequest;
use App\Http\Requests\Admin\Inventory\IsolateQuarantineRequest;
use App\DTOs\Admin\Inventory\TransferSafetyDataDTO;
use App\DTOs\Admin\Inventory\IsolateQuarantineDataDTO;
use App\Actions\Admin\Inventory\Stock\ListInventoryBalancesAction;
use App\Actions\Admin\Inventory\Stock\GetSkuKardexAction;
use App\Actions\Admin\Inventory\Stock\GetSkuLotsAction;
use App\Actions\Admin\Inventory\Stock\TransferToSafetyAction;
use App\Actions\Admin\Inventory\Stock\IsolateToQuarantineAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InventoryController extends Controller
{
    public function index(Request $request, ListInventoryBalancesAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Stock/Index', [
            'balances' => $action->execute($request),
            'filters'  => $request->only(['search']),
        ]);
    }

    public function kardex(string $skuId, Request $request, GetSkuKardexAction $action): InertiaResponse
    {
        return Inertia::render('Admin/Inventory/Stock/Kardex', [
            'sku_id' => $skuId,
            'kardex' => $action->execute($skuId, $request),
        ]);
    }

    public function lots(string $skuId, Request $request, GetSkuLotsAction $action): JsonResponse
    {
        return response()->json([
            'lots' => $action->execute($skuId, $request)
        ]);
    }

    public function transferToSafety(TransferSafetyRequest $request, TransferToSafetyAction $action): RedirectResponse
    {
        $dto = TransferSafetyDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->back()
            ->with('success', 'Asignación de stock al fondo de contingencia ejecutada correctamente.');
    }

    public function isolateToQuarantine(IsolateQuarantineRequest $request, IsolateToQuarantineAction $action): RedirectResponse
    {
        $dto = IsolateQuarantineDataDTO::fromRequest($request);
        $adminId = (string) Auth::guard('super_admin')->id();

        $action->execute($dto, $adminId);

        return redirect()->back()
            ->with('success', 'Aislamiento preventivo y bloqueo de lote asentado en Kardex.');
    }
}