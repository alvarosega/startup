<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Price;

use App\Models\Inventory\Price;
use App\Models\Operations\Branch;
use App\Models\Catalog\Sku;
use Illuminate\Http\Request;

class ListPricesAction
{
    public function execute(Request $request): array
    {
        $prices = Price::with(['sku.product', 'branch'])
            ->where('deleted_epoch', 0)
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
                'valid_to' => $price->valid_to ? $price->valid_to->toDateTimeString() : null,
                'branch_name' => $price->branch ? (string) $price->branch->name : null,
                'sku_code' => $price->sku ? (string) $price->sku->code : null,
                'product_name' => $price->sku && $price->sku->product ? (string) $price->sku->product->name : null,
            ];
        }, $prices->items());

        return [
            'prices' => [
                'data' => $mappedItems,
                'next' => $prices->nextCursor()?->encode(),
                'prev' => $prices->previousCursor()?->encode(),
            ],
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray(),
            'skus' => Sku::where('is_active', true)->with('product:id,name')->get()->map(fn($s) => [
                'id' => (string) $s->id,
                'display' => "{$s->product->name} [{$s->code}]"
            ])->toArray(),
        ];
    }
}