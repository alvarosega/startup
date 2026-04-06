<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use App\DTOs\Admin\Order\ReviewPaymentDTO;
use App\Services\Inventory\InventoryOrchestrator; // <- INYECTAR CEREBRO LOGÍSTICO
use Illuminate\Support\Facades\DB;
use Exception;

class ApproveOrderPaymentAction
{
    public function __construct(protected InventoryOrchestrator $inventory) {}

    public function execute(ReviewPaymentDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            // Precargar items para evitar N+1 al descontar inventario
            $order = Order::with('items')->where('id', $dto->orderId)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'payment_pending') {
                throw new Exception('El pago de esta orden no está en revisión.');
            }

            // 1. CONFIRMAR INVENTARIO (Resta físico y elimina reserva)
            foreach ($order->items as $item) {
                $this->inventory->commitReservation($item->sku_id, $order->branch_id, $item->quantity);
            }
            
            // 2. ACTUALIZAR ORDEN
            $order->update([
                'status' => 'preparing',
                'bank_reference' => $dto->bankReference,
                'reviewed_at' => now(),
                'reservation_expires_at' => null 
            ]);
        });
    }
}