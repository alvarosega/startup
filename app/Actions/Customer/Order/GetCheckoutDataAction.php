<?php

namespace App\Actions\Customer\Order;

use App\Actions\Customer\Cart\GetCustomerCartAction;
use App\Http\Resources\Customer\Cart\CartResource;
use App\Models\{Customer, CustomerAddress, Branch};
use App\Services\Order\OrderFinancialService;
use Exception;

class GetCheckoutDataAction
{
    public function __construct(
        protected GetCustomerCartAction $getCartAction,
        protected OrderFinancialService $financialService
    ) {}

    public function execute(string $customerId, string $branchId): array
    {
        $customer = Customer::findOrFail($customerId);
        $branch = Branch::findOrFail($branchId);
        $cartModel = $this->getCartAction->execute();

        if (!$cartModel || $cartModel->items->isEmpty()) {
            throw new Exception("Tu carrito está vacío.");
        }

        $cartResource = (new CartResource($cartModel))->resolve();
        $subtotal = (float) $cartResource['total_price'];

        // Calculamos las dos realidades posibles para que el usuario elija en Vue
        $pickupLogistics = $this->financialService->calculate($branch, $customer, $subtotal, 'pickup');
        $deliveryLogistics = $this->financialService->calculate($branch, $customer, $subtotal, 'delivery');

        return [
            'cart'               => $cartResource,
            'pickup_logistics'   => $pickupLogistics,
            'delivery_logistics' => $deliveryLogistics,
            // Enviamos las direcciones solo para mostrar el nombre/alias en la UI si lo deseas
            'addresses'          => $customer->addresses()->where('is_active', true)->get(),
            'customer_location'  => [
                'lat' => $customer->latitude,
                'lng' => $customer->longitude,
            ],
            'config' => [
                'max_delivery_threshold' => 200.00,
                'reservation_minutes'    => 10
            ]
        ];
    }
}