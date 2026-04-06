<?php

declare(strict_types=1);

namespace App\Actions\Customer\Order\States;

use App\Models\Order;
use App\Http\Resources\Customer\Order\States\RejectedOrderResource;

class GetRejectionDataAction
{
    public function execute(string $orderId, string $customerId): array
    {
        $order = Order::where('customer_id', $customerId)->findOrFail($orderId);

        return [
            'order' => (new RejectedOrderResource($order))->resolve()
        ];
    }
}