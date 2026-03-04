<?php

namespace App\Actions\Driver\Dashboard;

use App\Models\Order;

class GetPendingOrdersAction
{
    public function execute(string $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('branch_id', $branchId)
            ->where('status', 'preparing')
            ->whereNull('driver_id')
            ->where('delivery_type', 'delivery')
            ->orderBy('created_at', 'asc') // Los más antiguos primero
            ->get(['id', 'code', 'delivery_data', 'delivery_fee', 'created_at', 'total_amount']);
    }
}