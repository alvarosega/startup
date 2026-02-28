<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\DTOs\Admin\Order\ReviewPaymentDTO;
use Illuminate\Support\Facades\DB;
use Exception;

class ApproveOrderPaymentAction
{
    public function execute(ReviewPaymentDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $order = Order::where('id', $dto->orderId)->lockForUpdate()->firstOrFail();

            if ($dto->type === 'advance') {
                if ($order->advance_status !== 'under_review') throw new Exception('El anticipo no está en revisión.');
                
                $order->update([
                    'advance_status' => 'approved',
                    'bank_reference' => $dto->bankReference,
                    'status' => 'preparing', // Mueve la logística
                    'reviewed_at' => now(),
                    'reservation_expires_at' => null // Detiene el reloj de expiración definitivamente
                ]);
            } else {
                if ($order->balance_status !== 'under_review') throw new Exception('El saldo no está en revisión.');
                
                $order->update([
                    'balance_status' => 'approved',
                    // Podríamos concatenar la referencia del saldo si el banco es distinto, por ahora sobrescribe o ignora.
                    'bank_reference' => $order->bank_reference . ' | ' . $dto->bankReference,
                ]);
            }
        });
    }
}