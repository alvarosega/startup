<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order;

use App\Models\Order;

class GetOrderTrackingDataAction
{
    public function execute(string $orderId): array
    {
        // Cargamos la relación del driver para extraer sus datos
        $order = Order::with(['driver.profile', 'branch:id,name'])->findOrFail($orderId);

        return [
            'order' => [
                'id'           => $order->id,
                'code'         => $order->code,
                'status'       => $order->status,
                'delivery_type'=> $order->delivery_type,
                // CRÍTICO: El cliente necesita ver este PIN para dictárselo al Driver
                'delivery_otp' => $order->delivery_otp, 
                
                'driver'       => $order->driver ? [
                    'name'  => $order->driver->profile->first_name ?? 'Repartidor',
                    'phone' => $order->driver->phone ?? 'No disponible',
                ] : null,
                
                'branch'       => $order->branch
            ]
        ];
    }
}