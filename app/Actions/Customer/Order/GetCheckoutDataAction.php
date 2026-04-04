<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order;

use App\Actions\Customer\Cart\GetCustomerCartAction;
use App\Models\{Customer, Branch};
use App\Services\Order\OrderFinancialService;
use Exception;

class GetCheckoutDataAction
{
    public function __construct(
        protected GetCustomerCartAction $getCartAction,
        protected OrderFinancialService $financialService
    ) {}

    public function execute(string $guestUuid, string $customerId, string $branchId): array
    {
        $customer = Customer::findOrFail($customerId);
        $branch = Branch::findOrFail($branchId);
        
        // RECTIFICACIÓN: Paso de 3 argumentos para evitar ArgumentCountError
        $cart = $this->getCartAction->execute($guestUuid, $customerId, $branchId);

        if (empty($cart['items'])) {
            throw new Exception("Tu carrito está vacío.");
        }

        $subtotal = (float) $cart['total_price'];

        // LEY DE PERFIL: Usamos el modelo Customer directamente para el cálculo
        $pickupLogistics = $this->financialService->calculate($branch, $customer, $subtotal, 'pickup');
        $deliveryLogistics = $this->financialService->calculate($branch, $customer, $subtotal, 'delivery');

        return [
            'cart'               => $cart,
            'pickup_logistics'   => $pickupLogistics,
            'delivery_logistics' => $deliveryLogistics,
            'customer_location'  => [
                'lat' => $customer->latitude,
                'lng' => $customer->longitude,
            ],
            'payment_config' => [
                'qr_image'     => asset('assets/img/static_qr_payment.png'),
                'instructions' => 'Escanea el código y sube tu comprobante en la siguiente pantalla.'
            ],
            'config' => [
                'max_delivery_threshold' => 200.00,
                'reservation_minutes'    => 10
            ]
        ];
    }
}