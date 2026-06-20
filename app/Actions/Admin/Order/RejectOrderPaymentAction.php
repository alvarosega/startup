<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Exception;

readonly class RejectOrderPaymentAction
{
    public function __construct(private InventoryOrchestrator $inventory) {}

    public function execute(string $orderId, string $rejectionReason, string $rejectionAction): void
    {
        DB::transaction(function () use ($orderId, $rejectionReason, $rejectionAction) {
            $order = Order::with('items')->where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'payment_pending') {
                throw new Exception('El pago no está en revisión.');
            }

            if ($rejectionAction === 'cancel') {
                // Liberar el stock y matar la orden
                foreach ($order->items as $item) {
                    $this->inventory->release($item->sku_id, $order->branch_id, (int)$item->quantity);
                }
                $order->update([
                    'status'           => 'cancelled',
                    'rejection_reason' => $rejectionReason,
                    'reviewed_at'      => now()
                ]);
            } else {
                // Opción 'retry': Volver a pending, dar 15 minutos más y borrar comprobante
                $order->update([
                    'status'                 => 'pending',
                    'rejection_reason'       => $rejectionReason,
                    'proof_of_payment'       => null,
                    'reservation_expires_at' => now()->addMinutes(15),
                    'reviewed_at'            => now()
                ]);
            }
        });
    }
}