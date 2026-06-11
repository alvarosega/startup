<?php

declare(strict_types=1);

namespace App\Actions\Admin\Price;

use App\Models\Sku;
use Illuminate\Support\Collection;

class GetPricesBySkuAction
{
    public function execute(Sku $sku): Collection
    {
        return $sku->prices()
            ->where('deleted_epoch', 0)
            ->where(fn($query) => $query->whereNull('valid_to')->orWhere('valid_to', '>=', now()))
            ->with(['branch:id,name', 'updater:id,first_name'])
            ->orderBy('branch_id')
            ->orderByDesc('priority')
            ->get()
            ->map(fn($price) => [
                'id'           => $price->id,
                'branch_id'    => $price->branch_id,
                'branch_name'  => $price->branch?->name,
                'type'         => $price->type,
                'list_price'   => $price->list_price,
                'final_price'  => $price->final_price,
                'min_quantity' => $price->min_quantity,
                'valid_from'   => $price->valid_from->toIso8601String(),
                'valid_to'     => $price->valid_to?->toIso8601String(),
                'updated_by'   => $price->updater?->first_name
            ]);
    }
}