<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Http\Resources\Admin\Order\AdminOrderResource;

class GetReadyForDispatchDataAction
{
    public function execute(string $orderId): array
    {
        // RECTIFICACIÓN: Carga ansiosa completa para evitar consultas N+1
        $order = Order::with([
            'customer.profile', 
            'branch:id,name', 
            'driver.profile', // CRÍTICO
            'items'
        ])->findOrFail($orderId);

        return [
            // Usamos el Resource para que la estructura sea idéntica en todo el Admin
            'order' => (new AdminOrderResource($order))->resolve()
        ];
    }
}