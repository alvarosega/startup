<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use Exception;

class DispatchOrderAction
{
    public function execute(string $orderId): void
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->status !== 'preparing') {
             throw new Exception('La orden debe estar preparada antes de ser despachada.');
        }

        // Importante: No debe avanzar a dispatched si no existe un Driver asignado (Opcional pero recomendable).
        if (!$order->driver_id && $order->delivery_type === 'delivery') {
             throw new Exception('No hay un conductor asigando a esta orden todavía.');
        }
        
        $order->update(['status' => 'dispatched']);
    }
}