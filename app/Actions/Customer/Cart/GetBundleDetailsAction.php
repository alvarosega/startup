<?php

namespace App\Actions\Customer\Cart;

use App\Models\Bundle;
use App\Services\ShopContextService;
use Carbon\Carbon;

class GetBundleDetailsAction
{
    public function __construct(
        protected ShopContextService $contextService
    ) {}

    public function execute(Bundle $bundle, string $branchId): Bundle
    {
        $defaultBranchId = $this->contextService->getDefaultBranchId();
        $now = Carbon::now();

        return $bundle->load([
            'skus.product', 
            // REPLICA EXACTA DE SQL DE TU PriceResolverService
            'skus.prices' => function($q) use ($branchId, $defaultBranchId, $now) {
                $q->whereIn('branch_id', [$branchId, $defaultBranchId])
                  ->where('valid_from', '<=', $now)
                  ->where(function ($query) use ($now) {
                      $query->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
                  })
                  // Eliminamos 'is_active' porque tu Service no lo usa
                  // Ordenamos exactamente igual que el Service
                  ->orderBy('priority', 'desc')
                  ->orderBy('min_quantity', 'asc'); 
            }
        ]);
    }
}