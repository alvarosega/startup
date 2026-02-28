<?php

namespace App\Actions\Admin\Inventory;

use App\DTOs\Admin\Inventory\PurchaseFilterDTO;
use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPurchasesAction
{
    public function execute(PurchaseFilterDTO $filters): LengthAwarePaginator
    {
        $query = Purchase::query()->with([
            'provider:id,commercial_name',
            'branch:id,name',
            'admin:id,first_name,last_name',
            'inventoryLots', // Cargamos la relación base
            'inventoryLots.sku:id,product_id,name,code', // Llave product_id es OBLIGATORIA aquí
            'inventoryLots.sku.product:id,name'
        ]);

        if ($filters->branch_id) {
            $query->where('branch_id', $filters->branch_id);
        }

        if ($filters->type) {
            $query->where(function ($q) use ($filters) {
                if ($filters->type === 'RELOT') {
                    $q->where('document_number', 'like', 'EMG-%');
                } else {
                    $q->where('document_number', 'like', 'CMP-%')
                      ->orWhere('document_number', 'like', 'INI-%');
                }
            });
        }

        return $query
            ->orderBy('branch_id')
            ->orderBy('purchase_date', 'desc')
            ->paginate($filters->per_page);
    }
}