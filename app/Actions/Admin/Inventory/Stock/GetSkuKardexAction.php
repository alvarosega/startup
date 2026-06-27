<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Stock;

use App\Models\Inventory\InventoryMovement;
use Illuminate\Http\Request;

class GetSkuKardexAction
{
    public function execute(string $skuId, Request $request): array
    {
        $branchId = $request->query('branch_id') ? (string) $request->query('branch_id') : null;

        $movements = InventoryMovement::with(['admin:id,first_name,last_name', 'lot:id,lot_code'])
            ->where('sku_id', $skuId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(30)
            ->withQueryString();

        $mappedItems = array_map(function ($mov) {
            return [
                'id'            => (string) $mov->id,
                'type'          => (string) $mov->type,
                'quantity'      => (float) $mov->quantity,
                'balance_after' => (float) $mov->balance_after,
                'reference'     => $mov->reference ? (string) $mov->reference : null,
                'reason'        => $mov->reason ? (string) $mov->reason : null,
                'created_at'    => $mov->created_at->toDateTimeString(),
                'lot_code'      => $mov->lot ? (string) $mov->lot->lot_code : null,
                'admin_name'    => $mov->admin ? "{$mov->admin->first_name} {$mov->admin->last_name}" : null,
            ];
        }, $movements->items());

        return [
            'data' => $mappedItems,
            'next' => $movements->nextCursor()?->encode(),
            'prev' => $movements->previousCursor()?->encode(),
        ];
    }
}