<?php

declare(strict_types=1);

namespace App\Actions\Driver\Dashboard;

use App\Models\Driver;
use App\Models\Order;

class GetDriverDashboardDataAction
{
    public function execute(Driver $driver): array
    {
        $activeOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['dispatched', 'arrived'])
            ->with('customer:id,phone')
            ->first([
                'id', 
                'code', 
                'delivery_data', 
                'delivery_fee', 
                'total_amount', 
                'payment_method', 
                'status', 
                'customer_id'
            ]);

        return [
            'activeOrder' => $activeOrder, 
        ];
    }
}