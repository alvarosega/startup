<?php

declare(strict_types=1);

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Operations\Branch;
use App\Models\Operations\Provider;
use App\Models\Catalog\Sku;

class GetPurchaseFormOptionsAction
{
    public function execute(): array
    {
        return [
            'branches' => Branch::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($b) => ['id' => (string) $b->id, 'name' => (string) $b->name])
                ->toArray(),
                
            'providers' => Provider::where('is_active', true)
                ->orderBy('company_name')
                ->get(['id', 'company_name'])
                ->map(fn($p) => ['id' => (string) $p->id, 'company_name' => (string) $p->company_name])
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