<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\DTOs\Admin\Order\ReviewPaymentDTO;
use Illuminate\Support\Facades\DB;
use Exception;

class RejectOrderPaymentAction
{
    public function execute(ReviewPaymentDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $order = Order::where('id', $dto->orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'under_review') {
                throw new Exception('El pago de esta orden no está en revisión.');
            }

            $order->update([
                'status' => 'pending_payment', // Lo regresamos al cliente
                'rejection_reason' => $dto->rejectionReason,
                'proof_of_payment' => null, // Borramos el comprobante malo de la BD
                'reservation_expires_at' => now()->addMinutes(10) // Le damos 10 min extra
            ]);
        });
    }
}