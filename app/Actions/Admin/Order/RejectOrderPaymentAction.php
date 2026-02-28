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

            if ($dto->type === 'advance') {
                $order->update([
                    'advance_status' => 'rejected',
                    'rejection_reason' => $dto->rejectionReason,
                    'advance_proof' => null, // Purgamos la imagen mala
                    'status' => 'pending_payment', // Lo regresamos al estado inicial
                    'reservation_expires_at' => now()->addMinutes(10) // Le damos 10 minutos más
                ]);
            } else {
                $order->update([
                    'balance_status' => 'rejected',
                    'rejection_reason' => $dto->rejectionReason,
                    'balance_proof' => null,
                ]);
            }
        });
    }
}