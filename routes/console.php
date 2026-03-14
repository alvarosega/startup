<?php

use App\Models\Order;
use App\Services\Order\OrderCancellationService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $cancellationService = app(OrderCancellationService::class);

    // Buscamos órdenes pendientes cuyo tiempo de reserva ya pasó
    $expiredOrders = Order::where('status', 'pending_payment')
        ->where('reservation_expires_at', '<', now())
        ->with('items')
        ->get();

    foreach ($expiredOrders as $order) {
        $cancellationService->handleExpiration($order);
    }
})->everyMinute();