<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Stock;

use App\Models\Inventory\InventoryBalance;
use Illuminate\Http\Request;

class ListInventoryBalancesAction
{
    public function execute(Request $request): array
    {
        $search = $request->query('search') ? trim((string) $request->query('search')) : null;

        $balances = InventoryBalance::with(['sku.product', 'branch'])
            ->when($search, function ($query, $s) {
                $query->whereHas('sku', function ($q) use ($s) {
                    $q->where('code', 'like', "%{$s}%")
                      ->orWhereHas('product', function ($p) use ($s) {
                          $p->where('name', 'like', "%{$s}%");
                      });
                });
            })
            ->orderBy('branch_id')
            ->cursorPaginate(15)
            ->withQueryString();

        $mappedItems = array_map(function ($balance) {
            $physical = (float) $balance->total_physical;
            $reserved = (float) $balance->total_reserved;
            $safety   = (float) $balance->total_safety;
            $quarantine = (float) $balance->total_quarantine;
            
            return [
                'branch_id'        => (string) $balance->branch_id,
                'sku_id'           => (string) $balance->sku_id,
                'branch_name'      => $balance->branch ? (string) $balance->branch->name : null,
                'product_name'     => $balance->sku && $balance->sku->product ? (string) $balance->sku->product->name : null,
                'sku_code'         => $balance->sku ? (string) $balance->sku->code : null,
                'total_physical'   => $physical,
                'total_reserved'   => $reserved,
                'total_safety'     => $safety,
                'total_quarantine' => $quarantine,
                'total_available'  => max(0.000, $physical - ($reserved + $safety + $quarantine)),
            ];
        }, $balances->items());

        return [
            'data'  => $mappedItems,
            'next'  => $balances->nextCursor()?->encode(),
            'prev'  => $balances->previousCursor()?->encode(),
            'query' => $request->query(),
        ];
    }
}