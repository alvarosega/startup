<?php

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute(Customer $customer, string $addressId): void
    {
        $address = $customer->addresses()->findOrFail($addressId);

        DB::transaction(function () use ($customer, $address) {
            // 1. Resetear estados
            $customer->addresses()->update(['is_default' => false]);

            // 2. Establecer nuevo default
            $address->update(['is_default' => true]);

            // 3. REPLICACIÓN INTEGRAL AL NÚCLEO
            $customer->update([
                'branch_id' => $address->branch_id,
                'latitude'  => $address->latitude,
                'longitude' => $address->longitude,
            ]);
        });
    }
}