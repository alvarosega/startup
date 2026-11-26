<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Removal;

use App\Models\Inventory\RemovalRequest;
use Illuminate\Http\Request;

class ListRemovalsAction
{
    public function execute(Request $request): array
    {
        $search = $request->query('search') ? trim((string) $request->query('search')) : null;

        $removals = RemovalRequest::with(['branch:id,name', 'admin:id,first_name,last_name', 'items.lot.sku.product'])
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('code', 'like', "%{$term}%")
                      ->orWhere('reason', 'like', "%{$term}%")
                      ->orWhere('notes', 'like', "%{$term}%")
                      ->orWhereHas('branch', function ($bq) use ($term) {
                          $bq->where('name', 'like', "%{$term}%");
                      })
                      ->orWhereHas('items.lot.sku', function ($sq) use ($term) {
                          $sq->where('code', 'like', "%{$term}%")
                             ->orWhereHas('product', function ($pq) use ($term) {
                                 $pq->where('name', 'like', "%{$term}%");
                             });
                      });
                });
            })
            ->orderBy('id', 'desc')
            ->cursorPaginate(15)
            ->withQueryString();

        $mappedItems = array_map(function ($removal) {
            return [
                'id' => (string) $removal->id,
                'code' => (string) $removal->code,
                'status' => (string) $removal->status,
                'reason' => (string) $removal->reason,
                'notes' => $removal->notes,
                'approved_at' => $removal->approved_at->toDateTimeString(),
                'branch_name' => $removal->branch ? (string) $removal->branch->name : null,
                'admin_name' => $removal->admin ? "{$removal->admin->first_name} {$removal->admin->last_name}" : null,
                'items' => $removal->items->map(fn($item) => [
                    'id' => (string) $item->id,
                    'lot_code' => $item->lot ? (string) $item->lot->lot_code : null,
                    'sku_code' => $item->lot && $item->lot->sku ? (string) $item->lot->sku->code : null,
                    'product_name' => $item->lot && $item->lot->sku && $item->lot->sku->product ? (string) $item->lot->sku->product->name : null,
                    'quantity' => (float) $item->quantity,
                    'unit_cost' => (float) $item->unit_cost,
                    'total_loss' => (float) ($item->quantity * $item->unit_cost),
                ])->toArray(),
            ];
        }, $removals->items());

        return [
            'data' => $mappedItems,
            'next' => $removals->nextCursor()?->encode(),
            'prev' => $removals->previousCursor()?->encode(),
        ];
    }
}