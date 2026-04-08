<?php

declare(strict_types=1);

namespace App\Actions\Customer\Orders;

use App\DTOs\Customer\Orders\TransitionToPaymentPendingDTO;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class TransitionToPaymentPendingAction
{
    /**
     * Ejecuta la transición de estado garantizando aislamiento transaccional.
     */
    public function execute(TransitionToPaymentPendingDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // 1. Candado Pesimista: Bloquea la orden para este proceso
            $order = Order::where('id', $dto->orderId)
                ->where('customer_id', $dto->customerId)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw new Exception("La orden especificada no existe o no te pertenece.");
            }

            // 2. Validación Estricta de Máquina de Estados
            if ($order->status !== 'pending') {
                throw new Exception("Transición rechazada: La orden ya no está en estado de espera de pago.");
            }

            // 3. Validación de Reloj Atómico (Fallo de Cron)
            // Si el cron falló o va con retraso, detenemos el proceso manualmente.
            if ($order->reservation_expires_at < now()) {
                throw new Exception("El tiempo de reserva ha expirado. El sistema cancelará la orden en breve.");
            }

            // 4. Estrategia de Almacenamiento (Disco Privado / Estructura por fechas)
            $date = now();
            $folder = sprintf('orders/%s/%s/%s', $date->format('Y'), $date->format('m'), $date->format('d'));
            $extension = $dto->proofFile->getClientOriginalExtension();
            $filename = sprintf('%s.%s', $order->id, $extension);

            // Almacenamos en el disco 'local' (storage/app/orders/...) que no es accesible por URL pública
            $path = $dto->proofFile->storeAs($folder, $filename, 'local');

            if (!$path) {
                throw new Exception("Error de I/O: No se pudo escribir el comprobante en el disco de seguridad.");
            }

            // 5. Transición Atómica
            $order->update([
                'proof_of_payment' => $path,
                'status'           => 'payment_pending'
            ]);

            return $order;
        });
    }
}