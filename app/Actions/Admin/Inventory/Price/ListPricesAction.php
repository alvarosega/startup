<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Price;

use App\Models\Inventory\Price;
use Illuminate\Http\Request;

class ListPricesAction
{
    public function execute(Request $request): array
    {
        $search = $request->query('search') ? trim((string) $request->query('search')) : null;

        $prices = Price::with(['sku.product', 'branch'])
            ->where('deleted_epoch', 0)
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->whereHas('sku', function ($sq) use ($term) {
                        $sq->where('code', 'like', "%{$term}%")
                           ->orWhereHas('product', function ($pq) use ($term) {
                               $pq->where('name', 'like', "%{$term}%");
                           });
                    })->orWhereHas('branch', function ($bq) use ($term) {
                        $bq->where('name', 'like', "%{$term}%");
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->cursorPaginate(15)
            ->withQueryString();

        $mappedItems = array_map(function ($price) {
            return [
                'id' => (string) $price->id,
                'type' => (string) $price->type,
                'list_price' => (float) $price->list_price,
                'final_price' => (float) $price->final_price,
                'min_quantity' => (int) $price->min_quantity,
                'priority' => (int) $price->priority,
                'valid_from' => $price->valid_from->toDateTimeString(),
                'valid_to' => $price->valid_to ? $price->valid_to->toDateTimeString() : 'Indefinido (∞)',
                'branch_name' => $price->branch ? (string) $price->branch->name : null,
                'sku_code' => $price->sku ? (string) $price->sku->code : null,
                'product_name' => $price->sku && $price->sku->product ? (string) $price->sku->product->name : null,
            ];
        }, $prices->items());

        return [
            'data' => $mappedItems,
            'next' => $prices->nextCursor()?->encode(),
            'prev' => $prices->previousCursor()?->encode(),
        ];
    }
}