<?php

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\{Branch, Provider, Sku};

class GetPurchaseFormDataAction
{
    public function execute(?string $adminBranchId = null): array
    {
        return [
            'branches'  => $adminBranchId 
                ? Branch::where('id', $adminBranchId)->get(['id', 'name'])
                : Branch::active()->get(['id', 'name']),
                
            'providers' => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name']),
            
            // Optimización de Memoria: select() antes del get()
            'skus' => Sku::query()
                ->select(['id', 'product_id', 'name', 'code'])
                ->with('product:id,name')
                ->where('is_active', true)
                ->get()
                ->map(fn($s) => [
                    'id'        => $s->id,
                    'full_name' => ($s->product->name ?? 'N/A') . " - {$s->name}",
                    'code'      => $s->code
                ])
        ];
    }
    /**
     * Obtiene solo las sucursales para los filtros del Index
     */
    public function getBranchesForFilter()
    {
        return Branch::active()->get(['id', 'name']);
    }
}