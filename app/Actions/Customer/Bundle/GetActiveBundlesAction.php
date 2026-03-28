<?php

declare(strict_types=1);

namespace App\Actions\Customer\Bundle;

use App\Models\Bundle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetActiveBundlesAction
{
    public function execute(string $branchId): Collection
    {
        $cacheKey = "active_bundles_home_{$branchId}";

        return Cache::remember($cacheKey, 600, function () use ($branchId) {
            return Bundle::query()
                ->where('branch_id', $branchId)
                ->active() // Usando el scope que definimos antes
                ->orderBy('starts_at', 'desc')
                ->get();
        });
    }
}