<?php

declare(strict_types=1);

namespace App\Actions\Customer\Checkout;

use App\DTOs\Customer\Checkout\CheckoutCartDTO;
use App\Models\{Order, OrderItem, Cart, CheckoutSnapshot};
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Exception;

readonly class PlaceOrderAction
{
    public function __construct(private InventoryOrchestrator $inventoryOrchestrator) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $now = now();

            $snapshot = CheckoutSnapshot::where('customer_id', $dto->customerId)
            ->where('branch_id', $dto->branchId)
            ->where('expires_at', '>', $now)
            ->lockForUpdate()
            ->first();
        
        // 1. EL SNAPSHOT DEBE VALIDARSE PRIMERO
        if (!$snapshot) {
            throw new Exception("El presupuesto ha expirado o es inexistente.");
        }
        
        // 2. LOGISTICS_DATA YA ES ARRAY (Gracias al Cast y a quitar el json_encode en el controller)
        $allData = $snapshot->logistics_data;
        $financials = $allData[$dto->deliveryType] ?? null;
        
        if (!$financials || !($financials['is_available'] ?? false)) {
            throw new Exception("Configuración logística no disponible o inválida.");
        }

            // 2. Bloqueo de Carrito
            $cart = Cart::where('id', $snapshot->cart_id)->with('items.sku.product')->lockForUpdate()->first();
            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception("Integridad de carrito comprometida.");
            }

            $orderItemsToInsert = [];
            $itemsSubtotal = 0.0;

            foreach ($cart->items as $cartItem) {
                // Sincronía FEFO
                $this->inventoryOrchestrator->reserve($cartItem->sku_id, $dto->branchId, (int)$cartItem->quantity);
                
                $lineSubtotal = (float)$cartItem->price_at_addition * $cartItem->quantity;
                $itemsSubtotal += $lineSubtotal;

                $orderItemsToInsert[] = new OrderItem([
                    'sku_id'         => $cartItem->sku_id,
                    'product_name'   => $cartItem->sku->product->name,
                    'sku_name'       => $cartItem->sku->name,
                    // RECTIFICACIÓN: Usar la propiedad física del modelo SKU
                    'image_snapshot' => $cartItem->sku->image_path, 
                    'quantity'       => $cartItem->quantity,
                    'unit_price'     => $cartItem->price_at_addition,
                    'subtotal'       => $lineSubtotal
                ]);
            }

            // 3. Generación de Identidad
            $orderCode = sprintf("ORD-%s-%s%04d", date('ymd'), strtoupper(substr($dto->customerId, 0, 3)), rand(1000, 9999));

            // 4. Persistencia
            $order = Order::create([
                'code'                   => $orderCode,
                'customer_id'            => $dto->customerId,
                'branch_id'              => $dto->branchId,
                'delivery_type'          => $dto->deliveryType,
                'delivery_data'          => [
                    'lat'         => $allData['location_snapshot']['lat'] ?? null,
                    'lng'         => $allData['location_snapshot']['lng'] ?? null,
                    'distance_km' => $financials['distance_km'] ?? 0
                ], 
                'status'                 => 'pending',
                'reservation_expires_at' => $now->copy()->addMinutes(10),
                'items_subtotal'         => $itemsSubtotal,
                'delivery_fee'           => $financials['delivery_fee'],
                'service_fee'            => $financials['service_fee'],
                'total_amount'           => $financials['total_amount'],
                'payment_method'         => $dto->paymentMethod,
            ]);

            $order->items()->saveMany($orderItemsToInsert);

            // 5. Limpieza
            $cart->items()->delete();
            $cart->delete();
            $snapshot->delete();

            return $order;
        });
    }
}