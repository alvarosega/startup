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

            if ($order->status !== 'under_review') {
                throw new Exception('El pago de esta orden no está en revisión.');
            }
            
            $order->update([
                'status' => 'preparing', // El pago es válido, movemos a logística
                'bank_reference' => $dto->bankReference,
                'reviewed_at' => now(),
                'reservation_expires_at' => null // Detenemos el reloj de muerte
            ]);
        });
    }
}