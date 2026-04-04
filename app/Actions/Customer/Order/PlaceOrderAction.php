<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order;

use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Models\{Order, OrderItem, Cart, Branch, Customer};
use App\Services\Order\OrderFinancialService;
use App\Services\Finance\PriceResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PlaceOrderAction
{
    public function __construct(
        private PriceResolverService $priceResolver,
        private OrderFinancialService $financialService // Inyección para evitar discrepancias
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

            // 1. Procesamiento de Inventario
            foreach ($cart->items as $cartItem) {
                $sku = $cartItem->sku;
                $updated = DB::update(
                    "UPDATE inventory_balances SET total_physical = total_physical - ?, total_reserved = total_reserved + ? 
                     WHERE sku_id = ? AND branch_id = ? AND (total_physical - total_reserved) >= ?",
                    [$cartItem->quantity, $cartItem->quantity, $sku->id, $dto->branchId, $cartItem->quantity]
                );

                if ($updated === 0) {
                    throw new Exception("Stock insuficiente para '{$sku->name}'.");
                }

                $priceData = $this->priceResolver->resolveWinningPrice($sku, $cartItem->quantity, $now);
                $lineSubtotal = $priceData->final_price * $cartItem->quantity;
                $itemsSubtotal += $lineSubtotal;

                $orderItemsToInsert[] = new OrderItem([
                    'sku_id' => $sku->id,
                    'product_name' => $sku->product->name ?? 'Producto',
                    'sku_name' => $sku->name,
                    'image_snapshot' => $sku->image_path,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $priceData->final_price,
                    'subtotal' => $lineSubtotal
                ]);
            }

            // 2. ÚNICA FUENTE DE VERDAD: Cálculo financiero oficial
            $financials = $this->financialService->calculate($branch, $customer, $itemsSubtotal, $dto->deliveryType);

            if (!$financials['is_available']) {
                throw new Exception($financials['error_message']);
            }

            // 3. Persistencia de la Orden
            $order = Order::create([
                'code' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id' => $dto->customerId,
                'branch_id' => $dto->branchId,
                'delivery_type' => $dto->deliveryType,
                'delivery_data' => [
                    'latitude' => $customer->latitude,
                    'longitude' => $customer->longitude,
                    'distance_km' => $financials['distance_km']
                ],
                'status' => 'pending',
                'reservation_expires_at' => $now->copy()->addMinutes(10),
                'items_subtotal' => $itemsSubtotal,
                'delivery_fee' => $financials['delivery_fee'],
                'service_fee' => $financials['service_fee'],
                'total_amount' => $financials['total_amount'],
                'payment_method' => $dto->paymentMethod,
            ]);

            $order->items()->saveMany($orderItemsToInsert);
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }
}