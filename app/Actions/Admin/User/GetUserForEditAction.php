<?php

namespace App\Actions\Admin\User;

use App\Models\Customer;
use App\Models\Branch;

class GetUserForEditAction
{
    public function execute(string $id): array
    {
        // OBLIGATORIO: Cargar 'profile' para obtener los nombres
        $customer = Customer::with(['profile'])->findOrFail($id);

        return [
            'user'     => $customer,
            'branches' => Branch::all(['id', 'name'])
        ];
    }
}