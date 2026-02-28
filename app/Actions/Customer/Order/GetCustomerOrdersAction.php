<?php

namespace App\Actions\Customer\Order;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class GetCustomerOrdersAction
{
    public function execute(GetCustomerOrderDTO $dto): LengthAwarePaginator
    {
        return Order::where('customer_id', $dto->customerId)
            ->withCount('items')
            ->orderByDesc('created_at')
            ->paginate(10);
    }
}