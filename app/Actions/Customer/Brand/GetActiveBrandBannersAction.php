<?php

declare(strict_types=1);

namespace App\Actions\Customer\Brand;

use App\Models\AdCreative;
use Illuminate\Support\Collection;

class GetActiveBrandBannersAction
{
    public function execute(string $branchId): Collection
    {
        return AdCreative::query()
            ->with(['brand:id,name,slug'])
            ->where('branch_id', $branchId)
            ->whereHas('placement', fn($q) => $q->where('code', 'BRAND_HERO'))
            ->active()
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}