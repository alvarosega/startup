<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Models\Order;
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

readonly class OrderCancellationService
{
    public function __construct(private InventoryOrchestrator $inventoryOrchestrator) {}

    /**
     * Cancela la orden y libera el stock atómicamente.
     */
    public function handleExpiration(Order $unlockedOrder): void
    {
        try {
            DB::transaction(function () use ($unlockedOrder) {
                // 1. Bloqueo de fila para evitar colisión con el cliente subiendo un comprobante
                $order = Order::where('id', $unlockedOrder->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    return; // La orden ya fue procesada, pagada o cancelada por otro hilo
                }

                // 2. Liberación de Inventario (Delega al Orquestador)
                foreach ($order->items as $item) {
                    $this->inventoryOrchestrator->release($item->sku_id, $order->branch_id, (int) $item->quantity);
                }

                // 3. Transición de Estado
                $order->update([
                    'status' => 'cancelled',
                    'rejection_reason' => 'Tiempo de reserva agotado (Cancelación Automática)'
                ]);
                
                Log::info("Orden {$order->code} cancelada por expiración de tiempo.");
            });
        } catch (Exception $e) {
            Log::error("Fallo al expirar la orden {$unlockedOrder->code}: " . $e->getMessage());
        }
    }
}