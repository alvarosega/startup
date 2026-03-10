<?php

namespace App\Actions\Admin\Users\Customer;

use App\Models\Customer;
use App\Models\Branch;

class GetCustomerForEditAction
{
    public function execute(string $id): array
    {
        // Carga ansiosa de profile y la dirección por defecto
        $customer = Customer::with(['profile', 'addresses' => function ($query) {
            $query->where('is_default', true)->limit(1);
        }])->findOrFail($id);

        return [
            'user'     => $customer,
            'branches' => Branch::where('is_active', true)->get(['id', 'name'])
        ];
    }
}