<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order\States;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;
use App\Http\Resources\Customer\Order\States\PaymentWaitResource;

class GetPaymentWaitDataAction
{
    public function execute(GetCustomerOrderDTO $dto): array
    {
        // RECTIFICACIÓN: Uso de las propiedades del DTO en lugar de strings sueltos
        $order = Order::where('customer_id', $dto->customerId)->findOrFail($dto->orderId);

        return [
            'order' => (new PaymentWaitResource($order))->resolve()
        ];
    }
}