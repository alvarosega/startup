<?php

declare(strict_types=1);

namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Exception;

readonly class ApproveOrderPaymentAction
{
    public function __construct(private InventoryOrchestrator $inventory) {}

    public function execute(string $orderId, string $bankReference): void
    {
        DB::transaction(function () use ($orderId, $bankReference) {
            $order = Order::with('items')->where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'payment_pending') {
                throw new Exception('El pago no está en revisión.');
            }

            // 1. Descuento físico irreversible
            foreach ($order->items as $item) {
                $this->inventory->commitReservation($item->sku_id, $order->branch_id, (int)$item->quantity);
            }
            
            // 2. Transición a Preparación
            $order->update([
                'status'         => 'preparing',
                'bank_reference' => $bankReference,
                'reviewed_at'    => now(),
                'reviewed_by'    => auth()->guard('super_admin')->id(), // ACCOUNTABILITY
                'reservation_expires_at' => null 
            ]);
        });
    }
}