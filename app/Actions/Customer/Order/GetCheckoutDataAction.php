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

    /**
     * Prepara la data de la "Proforma" de compra.
     * * @throws Exception
     */
    public function execute(string $customerId, string $branchId): array
    {
        $customer = Customer::findOrFail($customerId);
        $branch = Branch::findOrFail($branchId);

        // 1. Recuperar el carrito con sus relaciones (Eager Loading)
        $cartModel = $this->getCartAction->execute();

        if (!$cartModel || $cartModel->items->isEmpty()) {
            throw new Exception("Tu carrito de compras está vacío.");
        }

        // 2. Transformar a Resource para asegurar que los precios dinámicos estén resueltos
        $cartResource = (new CartResource($cartModel))->resolve();
        $subtotal = (float) $cartResource['total_price'];

        // 3. Preparar Logística de PickUp (Retiro en Tienda)
        // Usamos el servicio pero con dirección null para forzar lógica de tienda
        $pickupLogistics = $this->financialService->calculate($branch, null, $customer, $subtotal, 'pickup');

        // 4. Preparar Direcciones con Logística de Envío Individual
        $addresses = CustomerAddress::where('customer_id', $customerId)
            ->where('is_active', true)
            // Priorizamos las direcciones que pertenecen a la cobertura de esta sucursal
            ->where('branch_id', $branchId) 
            ->get();

        $addressesWithFees = $addresses->map(function($address) use ($branch, $customer, $subtotal) {
            $logistics = $this->financialService->calculate($branch, $address, $customer, $subtotal, 'delivery');
            
            return [
                'id'           => (string) $address->id,
                'alias'        => (string) $address->alias,
                'address'      => (string) $address->address,
                'details'      => (string) $address->reference,
                'is_default'   => (bool) $address->is_default,
                'logistics'    => $logistics // Incluye is_available y error_message si supera los 200 Bs.
            ];
        });

        // 5. Estructura de salida atómica para la vista Vue
        return [
            'cart'               => $cartResource,
            'pickup_logistics'   => $pickupLogistics,
            'addresses'          => $addressesWithFees,
            'default_address_id' => $addressesWithFees->where('is_default', true)->first()['id'] ?? $addressesWithFees->first()['id'] ?? null,
            'config' => [
                'max_delivery_threshold' => 200.00,
                'reservation_minutes'    => 10
            ]
        ];
    }
}