<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Http\Resources\Admin\Order\AdminOrderResource;

class GetReadOnlyOrderDataAction
{
    /**
     * Extrae los datos inmutables de una orden finalizada para el silo Admin.
     * Carga de manera anticipada (Eager Loading) las identidades relacionadas.
     */
    public function execute(string $orderId): array
    {
        $order = Order::with([
            'items', 
            'customer.profile', 
            'branch', 
            'driver.profile'
        ])->findOrFail($orderId);

        return [
            // Se usa resolve() para omitir el data wrapping global de Laravel
            'order' => (new AdminOrderResource($order))->resolve()
        ];
    }
}