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
            ->whereIn('status', ['preparing', 'ready_for_dispatch', 'dispatched', 'arrived'])
            ->with('customer:id,phone')
            // RECTIFICADO: 'total_amount' en lugar de 'balance_amount' 
            // RECTIFICADO: 'payment_method' en lugar de 'payment_type'
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
                ->where('status', 'ready_for_dispatch') // Cambiado de 'preparing' a 'ready_for_dispatch' según tu lógica
                ->whereNull('driver_id')
                ->where('delivery_type', 'delivery')
                ->orderBy('created_at', 'asc')
                ->get(['id', 'code', 'delivery_data', 'delivery_fee', 'created_at']);
        }

        return $data;
    }
}