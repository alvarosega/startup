<?php

declare(strict_types=1);

namespace App\Actions\Customer\Bundle;

use App\Models\AdCreative;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetBundleBannersAction
{
    public function execute(string $branchId, string $bundleId): Collection
    {
        $cacheKey = "bundle_contextual_ads_v1_{$bundleId}_{$branchId}";

        return Cache::remember($cacheKey, 3600, function () use ($branchId, $bundleId) {
            return AdCreative::query()
                ->where('is_active', true)
                ->where('branch_id', $branchId)
                ->where('bundle_id', $bundleId)
                ->whereHas('placement', fn($q) => $q->where('code', 'BUNDLE_HERO'))
                ->whereHas('campaign', fn($q) => $q->where('is_active', true)) // Scope simplificado
                ->with(['target'])
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }
}