<?php

namespace App\Actions\Customer\Brand;

use App\Models\Brand;
use App\Services\ShopContextService;

final readonly class GetActiveBrandsAction
{
    public function __construct(private ShopContextService $contextService) {}

    public function execute(): \Illuminate\Database\Eloquent\Collection
    {
        $branchId = $this->contextService->getActiveBranchId();

        return Brand::query()
            ->active()
            ->whereHasStockInBranch($branchId) // Solo marcas con existencias reales
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->limit(6) // LEY DE DISEÑO: Solo el TOP 6 para el Radar
            ->get();
    }
}