<?php
namespace App\Actions\Admin\Inventory;
use App\Models\{Branch, Provider, Brand, Category};

class GetStockFilterDataAction {
    public function execute(?string $adminBranchId = null): array {
        return [
            'branches'   => $adminBranchId ? [] : Branch::active()->orderBy('name')->get(['id', 'name']),
            'providers'  => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name']),
            'brands'     => Brand::active()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
        ];
    }
}