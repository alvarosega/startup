<?php

namespace App\Actions\Customer\Order;

use App\Models\{Order, OrderItem, InventoryLot, Sku};
use App\DTOs\Customer\Order\CreateOrderDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // 1. Iniciar cabecera de la orden
            $order = Order::create([
                'code' => $this->generateUniqueCode(),
                'customer_id' => $dto->customerId,
                'branch_id' => $dto->branchId,
                'delivery_type' => $dto->deliveryType,
                'delivery_data' => $dto->deliveryData,
                'status' => 'pending_proof',
                'total_amount' => 0, // Se calculará dinámicamente
            ]);

            $totalAmount = 0;

            foreach ($dto->items as $item) {
                $sku = Sku::findOrFail($item['sku_id']);
                $requiredQuantity = $item['quantity'];

                // 2. Bloqueo de lotes (FEFO) para evitar Race Conditions
                $lots = InventoryLot::where('sku_id', $sku->id)
                    ->where('branch_id', $dto->branchId)
                    ->whereRaw('(quantity - reserved_quantity) > 0')
                    ->orderBy('expiration_date', 'asc') // FEFO
                    ->lockForUpdate() // BLOQUEO CRÍTICO
                    ->get();

                $availableStock = $lots->sum(fn($l) => $l->quantity - $l->reserved_quantity);

                if ($availableStock < $requiredQuantity) {
                    throw new Exception("Stock insuficiente para el SKU: {$sku->name}");
                }

                // 3. Reserva de stock en lotes
                $this->allocateStockFromLots($lots, $requiredQuantity);

                // 4. Registrar Item
                $subtotal = $sku->base_price * $requiredQuantity;
                OrderItem::create([
                    'order_id' => $order->id,
                    'sku_id' => $sku->id,
                    'quantity' => $requiredQuantity,
                    'unit_price' => $sku->base_price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            // 5. Actualización final de la orden
            $order->update(['total_amount' => $totalAmount]);

            return $order;
        });
    }

    private function allocateStockFromLots($lots, int $quantity): void
    {
        $remainingToAllocate = $quantity;

        foreach ($lots as $lot) {
            if ($remainingToAllocate <= 0) break;

            $availableInLot = $lot->quantity - $lot->reserved_quantity;
            $toReserve = min($remainingToAllocate, $availableInLot);

            $lot->increment('reserved_quantity', $toReserve);
            $remainingToAllocate -= $toReserve;
        }
    }

    private function generateUniqueCode(): string
    {
        // Generación de alto rendimiento: Prefijo + Timestamp Base36 + Random
        return 'ORD-' . strtoupper(base_convert(time(), 10, 36)) . '-' . strtoupper(Str::random(4));
    }
}