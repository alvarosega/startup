<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order;

use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Models\{Order, OrderItem, Cart, Branch, Customer};
use App\Services\Order\OrderFinancialService;
use App\Services\Finance\PriceResolverService;
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PlaceOrderAction
{
    public function __construct(
        private PriceResolverService $priceResolver,
        private OrderFinancialService $financialService,
        private InventoryOrchestrator $inventoryOrchestrator // RECTIFICACIÓN: Inyección de Cerebro Logístico
    ) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $customer = Customer::findOrFail($dto->customerId);
            $branch = Branch::findOrFail($dto->branchId);
            
            $cart = Cart::where('customer_id', $dto->customerId)
                        ->where('branch_id', $dto->branchId)
                        ->with('items.sku.product')
                        ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception("El carrito ha caducado.");
            }

            $now = now();
            $itemsSubtotal = 0;
            $orderItemsToInsert = [];

            // 1. Procesamiento de Inventario y Precios
            foreach ($cart->items as $cartItem) {
                $sku = $cartItem->sku;
                
                // RECTIFICACIÓN: Delegamos la reserva al orquestador para mantener sincronía entre Lotes (FEFO) y Balances
                try {
                    $this->inventoryOrchestrator->reserve(
                        $sku->id, 
                        $dto->branchId, 
                        (int) $cartItem->quantity
                    );
                } catch (Exception $e) {
                    throw new Exception("Stock insuficiente para '{$sku->name}' en los almacenes.");
                }

                $priceData = $this->priceResolver->resolveWinningPrice($sku, $cartItem->quantity, $now);
                $lineSubtotal = $priceData->final_price * $cartItem->quantity;
                $itemsSubtotal += $lineSubtotal;

                $orderItemsToInsert[] = new OrderItem([
                    'sku_id'         => $sku->id,
                    'product_name'   => $sku->product->name ?? 'Producto',
                    'sku_name'       => $sku->name,
                    'image_snapshot' => $sku->image_path,
                    'quantity'       => $cartItem->quantity,
                    'unit_price'     => $priceData->final_price,
                    'subtotal'       => $lineSubtotal
                ]);
            }

            // 2. ÚNICA FUENTE DE VERDAD: Cálculo financiero oficial
            $financials = $this->financialService->calculate($branch, $customer, $itemsSubtotal, $dto->deliveryType);

            if (!$financials['is_available']) {
                throw new Exception($financials['error_message']);
            }

            // 3. Persistencia de la Orden
            $order = Order::create([
                'code'                   => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id'            => $dto->customerId,
                'branch_id'              => $dto->branchId,
                'delivery_type'          => $dto->deliveryType,
                'delivery_data'          => [
                    'latitude'         => $customer->latitude,
                    'longitude'        => $customer->longitude,
                    'distance_km'      => $financials['distance_km'],
                    // RECTIFICACIÓN: Snapshot para evitar vacíos en la vista
                    'address_snapshot' => "Lat: {$customer->latitude}, Lng: {$customer->longitude}" 
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
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }
}