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
            ->whereIn('status', ['preparing', 'dispatched', 'arrived', 'delivering'])
            ->with('customer:id,phone')
            ->first(['id', 'code', 'delivery_data', 'delivery_fee', 'balance_amount', 'payment_type', 'status', 'customer_id', 'delivery_otp']);

        $data = [
            'driver' => [
                'id' => $driver->id, 
                'is_online' => (bool) $driver->is_online,
                'status' => $driver->status
            ],
            'branch' => $driver->branch ? ['lat' => $driver->branch->latitude, 'lng' => $driver->branch->longitude] : null,
            // CORRECCIÓN AQUÍ: Evitar clonar null
            'activeOrder' => $activeOrder ? clone $activeOrder : null, 
            'pendingOrders' => []
        ];

        // Si no tiene pedido activo, buscamos los pendientes en el radar
        if (!$activeOrder) {
             $data['pendingOrders'] = Order::where('branch_id', $driver->branch_id)
                ->where('status', 'preparing')
                ->whereNull('driver_id')
                ->where('delivery_type', 'delivery')
                ->get(['id', 'code', 'delivery_data', 'delivery_fee']);
        }

        return $data;
    }
}