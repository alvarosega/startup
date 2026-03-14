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
                ->lockForUpdate() 
                ->first();

            if (!$order) {
                throw new Exception('Operación inválida. La orden no está en estado de entrega.');
            }

            // Validación estricta del OTP
            if ($order->delivery_otp !== $dto->otp) {
                throw new Exception('Código PIN incorrecto. Solicítalo nuevamente al cliente.');
            }

            $order->update([
                'status' => 'completed',
                'completed_at' => now(), // Registro para KPIs de logística
                'delivery_otp' => null   // Limpieza de seguridad
            ]);
            
            // Nota: Aquí podrías liberar al conductor en Redis si fuera necesario, 
            // pero su estado 'is_online' lo mantiene en el radar para el siguiente pedido.
        });
    }
}