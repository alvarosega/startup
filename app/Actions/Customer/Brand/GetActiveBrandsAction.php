<?php

declare(strict_types=1);

namespace App\Actions\Customer\Brand;

use App\Models\Catalog\Brand;
use App\Services\ShopContextService;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetActiveBrandsAction
{
    public function __construct(private ShopContextService $contextService) {}

    public function execute(): Collection
    {
        $branchId = (string) $this->contextService->getActiveBranchId();

        return Brand::query()
            ->active()
            ->whereHasStockInBranch($branchId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->limit(6) 
            ->get();
    }
}