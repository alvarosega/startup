<?php

namespace App\Actions\Driver\Order;

use App\DTOs\Driver\Order\CompleteOrderDTO;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class CompleteOrderAction
{
    public function execute(CompleteOrderDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $order = Order::where('id', $dto->orderId)
                ->where('driver_id', $dto->driverId)
                ->where('status', 'arrived')
                ->lockForUpdate() // Evita race conditions
                ->first();

            if (!$order) {
                throw new Exception('Orden no válida para completar o estado incorrecto.');
            }

            if ($order->delivery_otp !== $dto->otp) {
                throw new Exception('Código PIN incorrecto. Intenta de nuevo.');
            }

            $order->update(['status' => 'completed']);
        });
    }
}