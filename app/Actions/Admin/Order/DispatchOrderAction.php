<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use Exception;

class DispatchOrderAction
{
    public function execute(string $orderId): void
    {
        $order = Order::where('id', $orderId)->firstOrFail();

        if ($order->status !== 'preparing') {
            throw new Exception('La orden debe estar en preparación para ser despachada.');
        }

        $order->update([
            'status' => 'dispatched'
        ]);
        
        // Aquí en el futuro se emitiría un evento para notificar al Silo Driver (Uber Light)
    }
}