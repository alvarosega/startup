<?php

declare(strict_types=1);

namespace App\Actions\Customer\Brand;

use App\Models\AdCreative;

class GetBrandHeroAction
{
    public function execute(string $brandId, string $branchId): ?AdCreative
    {
        return AdCreative::query()
            ->where('brand_id', $brandId)
            ->where('branch_id', $branchId)
            ->whereHas('placement', fn($q) => $q->where('code', 'BRAND_HERO'))
            ->active()
            ->first();
    }
}