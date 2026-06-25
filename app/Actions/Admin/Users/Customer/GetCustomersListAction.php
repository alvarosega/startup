<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCustomersListAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        return Customer::with(['profile', 'branch', 'addresses', 'billingInfos'])
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(25);
    }
}