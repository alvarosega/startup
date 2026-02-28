<?php

namespace App\Actions\Admin\Inventory;

use App\Models\{Branch, Provider, Sku};

class GetPurchaseFormDataAction
{
    /**
     * @param string|null $adminBranchId ID de la sucursal asignada al Admin (Silo de seguridad)
     */
    public function execute(?string $adminBranchId = null): array
    {
        $branches = $adminBranchId 
            ? Branch::where('id', $adminBranchId)->get(['id', 'name'])
            : Branch::active()->get(['id', 'name']);

        return [
            'branches'  => $branches,
            'providers' => Provider::active()->orderBy('commercial_name')->get(['id', 'commercial_name']),
            'skus'      => Sku::with('product:id,name')
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