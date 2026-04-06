<?php
declare(strict_types=1);
namespace App\Actions\Customer\Order\States;

use App\Models\Order;
use App\DTOs\Customer\Order\GetCustomerOrderDTO;
use App\Http\Resources\Customer\Order\States\StandardOrderResource;

class GetStandardOrderDataAction
{
    public function execute(GetCustomerOrderDTO $dto): array
    {
        $order = Order::where('customer_id', $dto->customerId)
            ->with(['items', 'branch'])
            ->findOrFail($dto->orderId);

        return ['order' => (new StandardOrderResource($order))->resolve()];
    }
}