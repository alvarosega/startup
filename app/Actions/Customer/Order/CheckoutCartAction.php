<?php

namespace App\Actions\Customer\Order;

use App\Models\{Cart, Order, OrderItem, InventoryLot, CustomerAddress, Customer};
use App\DTOs\Customer\Order\CheckoutCartDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CheckoutCartAction
{
    public function __construct(
        protected ResolvePriceAction $resolvePriceAction,
        protected CalculateDeliveryFeeAction $deliveryFeeAction
    ) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // 1. Bloqueo del Carrito (Previene doble click o concurrencia)
            $cart = Cart::where('customer_id', $dto->customerId)
                ->where('branch_id', $dto->branchId)
                ->with(['items.sku', 'branch']) 
                ->lockForUpdate()
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception("El carrito está vacío o ya fue procesado.");
            }

            $customer = Customer::findOrFail($dto->customerId);

            // 2. Pre-Cálculo de Productos (Necesario para el Motor Logístico)
            $subtotalProducts = 0;
            $itemsData = [];

            foreach ($cart->items as $cartItem) {
                $unitPrice = $this->resolvePriceAction->execute($cartItem->sku_id, $dto->branchId);
                $subtotal = $unitPrice * $cartItem->quantity;
                $subtotalProducts += $subtotal;

                $itemsData[] = [
                    'sku_id' => $cartItem->sku_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'name' => $cartItem->sku->name
                ];
            }

            // 3. Ejecución del Motor Logístico en el Servidor (Zero-Trust)
            $deliveryFee = 0.00;
            $serviceFee = 0.00;
            $deliveryData = [];
            
            if ($dto->deliveryType === 'delivery') {
                $address = CustomerAddress::where('id', $dto->addressId)
                    ->where('customer_id', $customer->id)
                    ->first();

                if (!$address) {
                    throw new Exception("La dirección de envío seleccionada no es válida.");
                }

                $logistics = $this->deliveryFeeAction->execute($cart->branch, $address, $customer, $subtotalProducts);
                
                $deliveryFee = $logistics['delivery_fee'];
                $serviceFee = $logistics['service_fee'];
                
                $deliveryData = [
                    'address_id' => $address->id,
                    'address' => $address->address,
                    'lat' => $address->latitude,
                    'lng' => $address->longitude,
                    'reference' => $address->reference,
                    'distance_km' => $logistics['distance_km']
                ];
            } else {
                // Cálculo para PickUp (Solo Tarifa de Servicio con Descuento)
                $baseServiceFee = $subtotalProducts * ($cart->branch->base_service_fee_percentage / 100);
                $trustScore = max(0, min(100, (int) $customer->trust_score)); 
                $loyaltyMultiplier = 1 - ($trustScore / 100);
                $serviceFee = round($baseServiceFee * $loyaltyMultiplier, 2);
            }

            $totalAmount = round($subtotalProducts + $deliveryFee + $serviceFee, 2);

            // 4. Inicialización de Orden Financieramente Precisa (100% Total)
            $order = Order::create([
                'id' => Str::uuid()->toString(),
                'code' => 'ORD-' . strtoupper(base_convert(time(), 10, 36)) . '-' . strtoupper(Str::random(4)),
                'customer_id' => $customer->id,
                'branch_id' => $cart->branch_id,
                'delivery_type' => $dto->deliveryType,
                'delivery_data' => empty($deliveryData) ? null : $deliveryData,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total_amount' => $totalAmount,
                'status' => 'pending_payment', 
                'reservation_expires_at' => now()->addMinutes(10),
            ]);

            // 5. Procesamiento de Items y Bloqueo de Inventario (FEFO)
            foreach ($itemsData as $item) {
                $lots = InventoryLot::where('sku_id', $item['sku_id'])
                    ->where('branch_id', $dto->branchId)
                    ->where('is_safety_stock', false) 
                    ->whereRaw('(quantity - reserved_quantity) > 0')
                    ->orderBy('expiration_date', 'asc')
                    ->lockForUpdate() // BLOQUEO CRÍTICO DE FILA
                    ->get();

                $availableStock = $lots->sum(fn($l) => $l->quantity - $l->reserved_quantity);

                if ($availableStock < $item['quantity']) {
                    throw new Exception("Stock insuficiente para el producto: {$item['name']}");
                }

                $remainingToReserve = $item['quantity'];
                foreach ($lots as $lot) {
                    if ($remainingToReserve <= 0) break;
                    
                    $availableInLot = $lot->quantity - $lot->reserved_quantity;
                    $toReserve = min($remainingToReserve, $availableInLot);
                    
                    $lot->increment('reserved_quantity', $toReserve);
                    $remainingToReserve -= $toReserve;
                }

                OrderItem::create([
                    'id' => Str::uuid()->toString(),
                    'order_id' => $order->id,
                    'sku_id' => $item['sku_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 6. Destrucción del Carrito Atómico
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }
}