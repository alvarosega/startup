<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\DTOs\Admin\Order\ReviewPaymentDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // <- IMPORTAR STORAGE
use Exception;

class RejectOrderPaymentAction
{
    public function execute(ReviewPaymentDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $order = Order::where('id', $dto->orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'payment_pending') { // RECTIFICADO
                throw new Exception('El pago de esta orden no está en revisión.');
            }

            // 1. LIMPIEZA ZERO-WASTE (Borrar archivo del disco)
            if ($order->proof_of_payment) {
                Storage::disk('public')->delete($order->proof_of_payment);
            }

            // 2. RETORNO DE ESTADO AL CLIENTE
            $order->update([
                'status' => 'pending', // RECTIFICADO: Vuelve al estado inicial
                'rejection_reason' => $dto->rejectionReason,
                'proof_of_payment' => null, 
                'reservation_expires_at' => now()->addMinutes(10) 
            ]);
        });
    }
}