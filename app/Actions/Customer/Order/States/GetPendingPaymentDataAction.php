<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order\States;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;
use App\Http\Resources\Customer\Order\States\PendingPaymentResource;

class GetPendingPaymentDataAction
{
    public function execute(GetCustomerOrderDTO $dto): array
    {
        $order = Order::where('customer_id', $dto->customerId)->findOrFail($dto->orderId);

        return [
            'order' => (new PendingPaymentResource($order))->resolve(),
            'payment_context' => [
                'seconds_remaining' => 600, // 10 min estáticos por ahora
                'qr_image' => asset('assets/images/qr_placeholder.png'),
                'bank_name' => 'ENTIDAD BANCARIA CENTRAL',
            ]
        ];
    }
}