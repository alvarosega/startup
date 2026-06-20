<?php

declare(strict_types=1);

namespace App\Actions\Customer\Bundle;

use App\Models\Bundle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetActiveBundlesAction
{
    public function execute(string $branchId, ?string $excludeId = null, ?string $type = null): Collection
    {
        // La llave de caché ahora es sensible a la exclusión y al tipo
        $cacheKey = "active_bundles_v3_{$branchId}_{$excludeId}_{$type}";

        return Cache::remember($cacheKey, 600, function () use ($branchId, $excludeId, $type) {
            $query = Bundle::query()
                ->where('branch_id', $branchId)
                ->active();

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if ($type) {
                $query->where('type', $type);
            }

            return $query->with([
                'skus' => fn($q) => $q->leftJoin('inventory_balances as ib', function ($j) use ($branchId) {
                    $j->on('skus.id', '=', 'ib.sku_id')->where('ib.branch_id', $branchId);
                })->addSelect(['skus.id', 'ib.total_physical', 'ib.total_reserved'])
            ])
            ->orderBy('starts_at', 'desc')
            ->get();
        });
    }
}