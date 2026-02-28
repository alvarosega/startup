<?php

namespace App\Actions\Customer\Cart;

use App\Models\Bundle;

class GetBundleDetailsAction
{
    public function execute(Bundle $bundle, string $branchId): Bundle
    {
        // La lógica de carga y filtrado vive aquí
        return $bundle->load([
            'skus.product', 
            'skus.prices' => fn($q) => $q->where('branch_id', $branchId)
        ]);
    }
}