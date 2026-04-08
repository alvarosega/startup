<?php

declare(strict_types=1);

namespace App\Actions\Customer\Checkout;

use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Models\{Order, OrderItem, Cart, CheckoutSnapshot};
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Exception;

readonly class PlaceOrderAction
{
    public function __construct(
        private InventoryOrchestrator $inventoryOrchestrator
    ) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $now = now();

            $snapshot = CheckoutSnapshot::where('customer_id', $dto->customerId)
                ->where('branch_id', $dto->branchId)
                ->where('expires_at', '>', $now)
                ->lockForUpdate()
                ->first();
            
            $allLogistics = json_decode($snapshot->logistics_data, true);
            // RECTIFICACIÓN: Extraer el nodo específico solicitado por el cliente
            $financials = $allLogistics[$dto->deliveryType] ?? null;
            
            if (!$financials || !$financials['is_available']) {
                throw new Exception("Configuración logística no válida o no disponible.");
            }

            if (!$snapshot) {
                throw new Exception("El presupuesto ha expirado. Vuelva a calcular el carrito.");
            }

            $logistics = json_decode($snapshot->logistics_data, true);

            // 2. Bloqueo de Carrito y Lectura de Datos Congelados
            $cart = Cart::where('id', $snapshot->cart_id)->with('items.sku.product')->lockForUpdate()->first();
            
            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception("El carrito asociado al presupuesto es inválido.");
            }

            $orderItemsToInsert = [];
            $itemsSubtotal = 0.0;

            // 3. Deducción Atómica de Inventario
            foreach ($cart->items as $cartItem) {
                $sku = $cartItem->sku;

                // Delegación al Orquestador (Que debe tener LOCK FOR UPDATE en inventory_balances)
                $this->inventoryOrchestrator->reserve($sku->id, $dto->branchId, (int) $cartItem->quantity);

                $lineSubtotal = (float) $cartItem->price_at_addition * $cartItem->quantity;
                $itemsSubtotal += $lineSubtotal;

                $orderItemsToInsert[] = new OrderItem([
                    'sku_id'         => $sku->id,
                    'product_name'   => $sku->product->name,
                    'sku_name'       => $sku->name,
                    'image_snapshot' => $sku->image_path,
                    'quantity'       => $cartItem->quantity,
                    'unit_price'     => $cartItem->price_at_addition,
                    'subtotal'       => $lineSubtotal
                ]);
            }

            // 4. Generación de Código Secuencial con Alta Entropía
            $microtime = substr((string) microtime(true), 11, 4);
            $orderCode = strtoupper(substr($dto->branchId, 0, 3)) . '-' . date('ymd') . '-' . rand(100, 999) . $microtime;

            // 5. Persistencia Inmutable de la Orden
            $order = Order::create([
                'code'                   => $orderCode,
                'customer_id'            => $dto->customerId,
                'branch_id'              => $dto->branchId,
                'delivery_type'          => $dto->deliveryType,
                'delivery_data'          => [
                    'lat'         => $snapshot->customer->latitude ?? null, // Requiere cargar relación en snapshot
                    'lng'         => $snapshot->customer->longitude ?? null,
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

            // 6. Limpieza de Trazabilidad
            $cart->items()->delete();
            $cart->delete();
            $snapshot->delete();

            return $order;
        });
    }
}