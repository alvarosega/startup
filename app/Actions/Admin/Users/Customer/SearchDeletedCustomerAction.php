<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;

class SearchDeletedCustomerAction
{
    public function execute(string $phone): ?Customer
    {
        return Customer::onlyTrashed()
            ->with(['profile', 'branch'])
            ->where('phone', $phone)
            ->first();
    }
}