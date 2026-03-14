<?php

namespace App\Actions\Customer\Order;

use App\Models\{Cart, Order, OrderItem, Customer, Branch};
use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Services\Order\OrderFinancialService;
use App\Services\Inventory\InventoryOrchestrator;
use App\Services\Finance\PriceResolverService; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaceOrderAction
{
    public function __construct(
        protected OrderFinancialService $financialService,
        protected InventoryOrchestrator $inventoryOrchestrator,
        protected PriceResolverService $priceResolver // <--- AÑADIR
    ) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $customer = Customer::findOrFail($dto->customerId);
            $branch   = Branch::findOrFail($dto->branchId);
            
            // 1. Cargar carrito con relaciones para el Snapshot
            $cart = Cart::where('customer_id', $customer->id)
                ->where('branch_id', $branch->id)
                ->with(['items.sku.product'])
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Resolver precios de items y calcular subtotal neto
            $itemsData = [];
            $itemsSubtotal = 0;

            foreach ($cart->items as $item) {
                // CAMBIO AQUÍ: de ->resolve(...) a ->resolveWinningPrice(...)
                $resolvedPrice = $this->priceResolver->resolveWinningPrice($item->sku, $branch->id, $item->quantity);
                
                $unitPrice = $resolvedPrice->final_price;
                $lineSubtotal = $unitPrice * $item->quantity;
            
                $itemsSubtotal += $lineSubtotal;

                $itemsData[] = [
                    'sku_id'         => $item->sku_id,
                    'product_name'   => $item->sku->product->name,
                    'sku_name'       => $item->sku->name,
                    'image_snapshot' => $item->sku->image_url,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $unitPrice,
                    'subtotal'       => $lineSubtotal,
                ];
            }

            // 3. Cálculo financiero global (Envío + Fee)
            $finances = $this->financialService->calculate($branch, $customer, $itemsSubtotal, $dto->deliveryType);

            if (!$finances['is_available']) {
                throw new \Exception($finances['error_message']);
            }

            // 4. Crear la Orden
            $order = Order::create([
                'id'            => Str::uuid(),
                'code'          => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id'   => $customer->id,
                'branch_id'     => $branch->id,
                'delivery_type' => $dto->deliveryType,
                'delivery_data' => [
                    'lat' => $customer->latitude,
                    'lng' => $customer->longitude,
                    'address_snapshot' => $customer->addresses()->where('is_default', true)->first()?->address,
                ],
                'items_subtotal' => $itemsSubtotal,
                'delivery_fee'   => $finances['delivery_fee'],
                'service_fee'    => $finances['service_fee'],
                'total_amount'   => $finances['total_amount'],
                'status'         => 'pending_payment',
                'payment_method' => $dto->paymentMethod,
                'reservation_expires_at' => now()->addMinutes(10),
            ]);

            // 5. Insertar Items con Snapshot y Reservar Inventario
            foreach ($itemsData as $data) {
                // Reservar en lotes FEFO
                $this->inventoryOrchestrator->reserve($data['sku_id'], $branch->id, $data['quantity']);

                // Crear el registro con todos los campos (Incluso unit_price)
                OrderItem::create(array_merge($data, ['order_id' => $order->id]));
            }

            // 6. Destrucción del Carrito
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }
}