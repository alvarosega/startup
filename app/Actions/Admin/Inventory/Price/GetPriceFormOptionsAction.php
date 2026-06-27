<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Price;

use App\Models\Operations\Branch;
use App\Models\Catalog\Sku;

class GetPriceFormOptionsAction
{
    public function execute(): array
    {
        return [
            'branches' => Branch::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($b) => ['id' => (string) $b->id, 'name' => (string) $b->name])
                ->toArray(),
                
            'skus' => Sku::where('is_active', true)
                ->with('product:id,name')
                ->get()
                ->map(fn($s) => [
                    'id' => (string) $s->id,
                    'display' => "{$s->product->name} [{$s->code}]"
                ])->toArray(),
        ];
    }
}