<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Removal;

use App\Models\Operations\Branch;
use App\Models\Inventory\InventoryLot;

class GetRemovalFormOptionsAction
{
    public function execute(): array
    {
        return [
            'branches' => Branch::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($b) => ['id' => (string) $b->id, 'name' => (string) $b->name])
                ->toArray(),

            'available_lots' => InventoryLot::with('sku.product')
                ->where('deleted_epoch', 0)
                ->where('quantity', '>', 0)
                ->where('is_quarantine', false)
                ->get()
                ->map(fn($lot) => [
                    'id' => (string) $lot->id,
                    'branch_id' => (string) $lot->branch_id,
                    'lot_code' => (string) $lot->lot_code,
                    'available_quantity' => (float) $lot->quantity,
                    'sku_display' => $lot->sku && $lot->sku->product 
                        ? "{$lot->sku->product->name} [{$lot->sku->code}]" 
                        : 'SKU Desconocido',
                ])
                ->toArray()
        ];
    }
}