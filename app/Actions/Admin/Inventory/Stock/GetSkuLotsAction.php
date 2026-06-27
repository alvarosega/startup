<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Stock;

use App\Models\Inventory\InventoryLot;
use Illuminate\Http\Request;

class GetSkuLotsAction
{
    public function execute(string $skuId, Request $request): array
    {
        $branchId = $request->query('branch_id') ? (string) $request->query('branch_id') : null;

        return InventoryLot::where('sku_id', $skuId)
            ->where('deleted_epoch', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($lot) => [
                'id'                => (string) $lot->id,
                'lot_code'          => (string) $lot->lot_code,
                'quantity'          => (float) $lot->quantity,
                'initial_quantity'  => (float) $lot->initial_quantity,
                'safety_quantity'   => (float) $lot->safety_quantity,
                'reserved_quantity' => (float) $lot->reserved_quantity,
                'cost_price'        => (float) $lot->cost_price,
                'is_quarantine'     => (bool) $lot->is_quarantine,
                'expiration_date'   => $lot->expiration_date ? $lot->expiration_date->toDateString() : null,
                'created_at'        => $lot->created_at->toDateTimeString(),
            ])
            ->toArray();
    }
}