<?php

namespace App\Actions\Customer\Order;

use App\Models\{Cart, Order, OrderItem, Customer, Branch, CustomerAddress};
use App\DTOs\Customer\Order\CheckoutCartDTO;
use App\Services\Order\OrderFinancialService;
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaceOrderAction
{
    public function __construct(
        protected OrderFinancialService $financialService,
        protected InventoryOrchestrator $inventoryOrchestrator
    ) {}

    public function execute(CheckoutCartDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // 1. HIDRATACIÓN DE ENTIDADES
            $customer = Customer::findOrFail($dto->customerId);
            $branch   = Branch::findOrFail($dto->branchId);
            $cart     = Cart::where('customer_id', $customer->id)
                ->where('branch_id', $branch->id)
                ->with('items.sku.product')
                ->lockForUpdate() // Evita cambios en el carrito mientras procesamos
                ->firstOrFail();

            $address = $dto->addressId ? CustomerAddress::find($dto->addressId) : null;

            // 2. CÁLCULO FINANCIERO FINAL (Zero-Trust)
            // No confiamos en lo que el frontend mande, recalculamos aquí.
            $subtotal = $cart->items->sum(fn($i) => $i->quantity * $i->unit_price);
            $finances = $this->financialService->calculate($branch, $address, $customer, $subtotal, $dto->deliveryType);

            if (!$finances['is_available']) {
                throw new \Exception($finances['error_message']);
            }

            // 3. CREACIÓN DE LA ORDEN (EL CONTRATO)
            $order = Order::create([
                'id'            => Str::uuid(),
                'code'          => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id'   => $customer->id,
                'branch_id'     => $branch->id,
                'delivery_type' => $dto->deliveryType,
                'delivery_data' => $this->mapDeliveryData($address, $finances),
                
                'items_subtotal' => $finances['items_subtotal'],
                'delivery_fee'   => $finances['delivery_fee'],
                'service_fee'    => $finances['service_fee'],
                'total_amount'   => $finances['total_amount'],
                
                'status'                 => 'pending_payment',
                'payment_method'         => $dto->paymentMethod,
                'reservation_expires_at' => now()->addMinutes(10),
            ]);

            // 4. PROCESAMIENTO DE ITEMS Y RESERVA DE STOCK
            foreach ($cart->items as $item) {
                // El orquestador maneja el FEFO y el lock de los lotes
                $this->inventoryOrchestrator->reserve($item->sku_id, $branch->id, $item->quantity);

                OrderItem::create([
                    'id'             => Str::uuid(),
                    'order_id'       => $order->id,
                    'sku_id'         => $item->sku_id,
                    'product_name'   => $item->sku->product->name, // SNAPSHOT
                    'sku_name'       => $item->sku->name,         // SNAPSHOT
                    'image_snapshot' => $item->sku->image_url,    // SNAPSHOT
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'subtotal'       => $item->quantity * $item->unit_price,
                ]);
            }

            // 5. LIMPIEZA
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }

    private function mapDeliveryData(?CustomerAddress $address, array $finances): ?array
    {
        if (!$address) return null;

        return [
            'address_id'  => $address->id,
            'address'     => $address->address,
            'latitude'    => $address->latitude,
            'longitude'   => $address->longitude,
            'reference'   => $address->reference,
            'distance_km' => $finances['distance_km'],
        ];
    }
}