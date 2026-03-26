<?php

namespace App\Actions\Admin\Inventory\Purchase;

use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\DTOs\Admin\Inventory\Purchase\PurchaseFilterDTO; 

class GetPurchasesAction
{
    public function execute(PurchaseFilterDTO $filters): LengthAwarePaginator
    {
        $query = Purchase::query()
            ->withCount('inventoryLots')
            ->with([
                'provider:id,commercial_name',
                'branch:id,name',
                'admin:id,first_name,last_name'
            ]);

        if ($filters->branch_id) {
            $query->where('branch_id', $filters->branch_id);
        }

        if ($filters->type) {
            $query->where('document_number', 'like', $filters->type === 'RELOT' ? 'EMG-%' : 'CMP-%');
        }

        return $query
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters->per_page);
    }
}