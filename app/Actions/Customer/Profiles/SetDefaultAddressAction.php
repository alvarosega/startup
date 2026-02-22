<?php

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute(Customer $customer, string $addressId): void
    {
        $address = $customer->addresses()->findOrFail($addressId);

        DB::transaction(function () use ($customer, $address) {
            // 1. Resetear todos los defaults del cliente
            $customer->addresses()->update(['is_default' => false]);

            // 2. Establecer la nueva dirección principal
            $address->update(['is_default' => true]);

            // 3. Sincronizar el branch_id en el cliente para el catálogo
            if ($address->branch_id) {
                $customer->update(['branch_id' => $address->branch_id]);
            }
        });
    }
}