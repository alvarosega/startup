<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLot;
use App\Models\Inventory\InventoryMovement;
use App\Http\Resources\Admin\Inventory\InventoryBalanceResource;
use App\Http\Resources\Admin\Inventory\InventoryLotResource;
use App\Http\Requests\Admin\Inventory\Adjustment\StoreTransferToSafetyRequest;
use App\Http\Requests\Admin\Inventory\Adjustment\StoreIsolateToQuarantineRequest;
use App\DTOs\Admin\Inventory\Adjustment\TransferToSafetyData;
use App\DTOs\Admin\Inventory\Adjustment\IsolateToQuarantineData;
use App\Actions\Admin\Inventory\Adjustment\TransferToSafety;
use App\Actions\Admin\Inventory\Adjustment\IsolateToQuarantine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

final class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $branchId = $request->input('branch_id');

        // Lectura de balances optimizada con carga previa de relaciones estructurales
        $query = InventoryBalance::with(['sku.product', 'branch']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $balances = $query->get();

        return Inertia::render('Admin/Inventory/Index', [
            'balances' => InventoryBalanceResource::collection($balances)->resolve(),
            'filters'  => $request->only(['branch_id'])
        ]);
    }

    public function lots(string $skuId, Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');

        $lots = InventoryLot::where('sku_id', $skuId)
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->orderBy('expiration_date', 'asc')
            ->get();

        return response()->json([
            'lots' => InventoryLotResource::collection($lots)->resolve()
        ]);
    }

    public function transferToSafety(StoreTransferToSafetyRequest $request, TransferToSafety $action): JsonResponse
    {
        $action->execute(TransferToSafetyData::fromRequest($request));

        return response()->json([
            'status'  => 'SUCCESS',
            'message' => 'PROTOCOLO_RESCATE: Unidades transferidas exitosamente al stock de seguridad inyectable.'
        ]);
    }

    public function isolateToQuarantine(StoreIsolateToQuarantineRequest $request, IsolateToQuarantine $action): JsonResponse
    {
        $action->execute(IsolateToQuarantineData::fromRequest($request));

        return response()->json([
            'status'  => 'SUCCESS',
            'message' => 'PROTOCOLO_AISLAMIENTO: Unidades removidas del flujo comercial y confinadas a cuarentena.'
        ]);
    }
}