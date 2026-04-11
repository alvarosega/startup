<?php

declare(strict_types=1);

namespace App\Actions\Customer\Checkout;

use App\DTOs\Customer\Checkout\CheckoutCartDTO;
use App\Models\{Order, OrderItem, Cart, CheckoutSnapshot, Branch, Customer};
use App\Services\Inventory\InventoryOrchestrator;
use App\Services\Order\OrderFinancialService; // INYECTAR SERVICIO
use Illuminate\Support\Facades\DB;
use Exception;

readonly class PlaceOrderAction
{
    public function __construct(
        private InventoryOrchestrator $inventoryOrchestrator,
        private OrderFinancialService $financialService // Inyectamos el motor financiero
    ) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $now = now();

            // 1. VALIDACIÓN DE SNAPSHOT
            $snapshot = CheckoutSnapshot::where('customer_id', $dto->customerId)
                ->where('branch_id', $dto->branchId)
                ->where('expires_at', '>', $now)
                ->lockForUpdate()
                ->first();
        
            if (!$snapshot) {
                throw new Exception("El protocolo de seguridad ha expirado. Por favor, reinicie el checkout.");
            }
            
            // 2. BLOQUEO Y CARGA DE CARRITO
            $cart = Cart::where('id', $snapshot->cart_id)
                ->with('items.sku.product')
                ->lockForUpdate()
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception("Integridad de hardware comprometida: El carrito está vacío.");
            }

            // 3. CÁLCULO FINANCIERO EN TIEMPO REAL (Seguridad Total)
            $itemsSubtotal = (float) $cart->items->sum(fn($item) => $item->price_at_addition * $item->quantity);
            
            $branch = Branch::findOrFail($dto->branchId);
            $customer = Customer::findOrFail($dto->customerId);

            // Recalculamos la logística elegida para asegurar que el precio es inmutable
            $financials = $this->financialService->calculate(
                $branch, 
                $customer, 
                $itemsSubtotal, 
                $dto->deliveryType
            );

            if (!$financials['is_available']) {
                throw new Exception("Falla Logística: " . ($financials['error_message'] ?? 'Servicio no disponible.'));
            }

            // 4. PROCESAMIENTO DE ITEMS E INVENTARIO
            $orderItemsToInsert = [];
            foreach ($cart->items as $cartItem) {
                // Reserva física de stock
                $this->inventoryOrchestrator->reserve($cartItem->sku_id, $dto->branchId, (int)$cartItem->quantity);
                
                $orderItemsToInsert[] = new OrderItem([
                    'sku_id'         => $cartItem->sku_id,
                    'product_name'   => $cartItem->sku->product->name,
                    'sku_name'       => $cartItem->sku->name,
                    'image_snapshot' => $cartItem->sku->image_path, 
                    'quantity'       => $cartItem->quantity,
                    'unit_price'     => $cartItem->price_at_addition,
                    'subtotal'       => (float)$cartItem->price_at_addition * $cartItem->quantity
                ]);
            }

            // 5. CREACIÓN DE ORDEN (Telemetría Final)
            $orderCode = sprintf("ORD-%s-%s%04d", date('ymd'), strtoupper(substr($dto->customerId, 0, 3)), rand(1000, 9999));

            $order = Order::create([
                'code'                   => $orderCode,
                'customer_id'            => $dto->customerId,
                'branch_id'              => $dto->branchId,
                'delivery_type'          => $dto->deliveryType,
                'delivery_data'          => [
                    'lat'         => $snapshot->logistics_data['location_snapshot']['lat'] ?? null,
                    'lng'         => $snapshot->logistics_data['location_snapshot']['lng'] ?? null,
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

            // 6. LIMPIEZA DE PROTOCOLO
            $cart->items()->delete();
            $cart->delete();
            $snapshot->delete();

            return $order;
        });
    }
}