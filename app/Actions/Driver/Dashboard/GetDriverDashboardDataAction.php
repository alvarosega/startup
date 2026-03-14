<?php

namespace App\Actions\Driver\Dashboard;

use App\Models\Driver;
use App\Models\Order;

class GetDriverDashboardDataAction
{
    public function execute(Driver $driver): array
    {
        $driver->load(['branch']);

        $activeOrder = Order::where('driver_id', $driver->id)
            // Agregamos 'preparing' porque ahora el driver lo toma en ese estado
            ->whereIn('status', ['preparing', 'dispatched', 'arrived'])
            ->with('customer:id,phone')
            // SEGURIDAD: Nunca incluyas 'delivery_otp' ni 'pickup_otp' aquí
            ->first(['id', 'code', 'delivery_data', 'delivery_fee', 'balance_amount', 'payment_type', 'status', 'customer_id']);

        $data = [
            'driver' => [
                'id' => $driver->id, 
                'is_online' => (bool) $driver->is_online,
                'status' => $driver->status
            ],
            'branch' => $driver->branch ? [
                'lat' => $driver->branch->latitude, 
                'lng' => $driver->branch->longitude,
                'name' => $driver->branch->name
            ] : null,
            'activeOrder' => $activeOrder, 
            'pendingOrders' => []
        ];

        if (!$activeOrder) {
             $data['pendingOrders'] = Order::where('branch_id', $driver->branch_id)
                ->where('status', 'preparing')
                ->whereNull('driver_id')
                ->where('delivery_type', 'delivery')
                ->orderBy('created_at', 'asc')
                ->get(['id', 'code', 'delivery_data', 'delivery_fee', 'created_at']);
        }

        return $data;
    }
}