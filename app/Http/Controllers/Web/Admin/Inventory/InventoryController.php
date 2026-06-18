<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\InventoryLot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Inventory/Index', [
            'branches' => Branch::select('id', 'name')->get()
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id']
        ]);

        $balances = InventoryBalance::with(['sku:id,name,code'])
            ->where('branch_id', $request->input('branch_id'))
            ->get()
            ->map(function ($balance) {
                return [
                    'sku_id'           => $balance->sku_id,
                    'sku_name'         => $balance->sku?->name,
                    'sku_code'         => $balance->sku?->code,
                    'total_physical'   => (float) $balance->total_physical,
                    'total_reserved'   => (float) $balance->total_reserved,
                    'total_quarantine' => (float) $balance->total_quarantine,
                    'total_safety'     => (float) $balance->total_safety,
                    'available'        => (float) ($balance->total_physical - $balance->total_reserved - $balance->total_quarantine)
                ];
            });

        return response()->json($balances, 200);
    }

    public function kardex(string $skuId, Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id']
        ]);

        $movements = InventoryMovement::with(['admin:id,name', 'lot:id,lot_code'])
            ->where('branch_id', $request->input('branch_id'))
            ->where('sku_id', $skuId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($movement) {
                return [
                    'id'         => $movement->id,
                    'type'       => $movement->type,
                    'quantity'   => (float) $movement->quantity,
                    'reference'  => $movement->reference,
                    'reason'     => $movement->reason,
                    'created_at' => $movement->created_at?->toIso8601String(),
                    'admin_name' => $movement->admin?->name,
                    'lot_code'   => $movement->lot?->lot_code
                ];
            });

        return response()->json($movements, 200);
    }

    public function lots(string $skuId, Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id']
        ]);

        $lots = InventoryLot::where('branch_id', $request->input('branch_id'))
            ->where('sku_id', $skuId)
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($lot) {
                return [
                    'id'                => $lot->id,
                    'lot_code'          => $lot->lot_code,
                    'quantity'          => (float) $lot->quantity,
                    'initial_quantity'  => (float) $lot->initial_quantity,
                    'reserved_quantity' => (float) $lot->reserved_quantity,
                    'is_safety_stock'   => (bool) $lot->is_safety_stock, // RECTIFICACIÓN
                    'is_quarantine'     => (bool) $lot->is_quarantine,   // RECTIFICACIÓN
                    'expiration_date'   => $lot->expiration_date?->format('Y-m-d'),
                    'is_expired'        => $lot->expiration_date ? $lot->expiration_date->isPast() : false
                ];
            });

        return response()->json($lots, 200);
    }
}