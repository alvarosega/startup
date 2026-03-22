<?php

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute(Customer $customer, string $addressId): void
    {
        DB::transaction(function () use ($customer, $addressId) {
            // Bloqueamos la dirección para asegurar que no sea eliminada durante el proceso
            $address = $customer->addresses()->where('id', $addressId)->lockForUpdate()->firstOrFail();

            // 1. Resetear flags
            $customer->addresses()->update(['is_default' => false]);

            // 2. Aplicar nuevo default
            $address->update(['is_default' => true]);

            // 3. Replicar al núcleo (User table)
            $customer->update([
                'branch_id' => $address->branch_id,
                'latitude'  => $address->latitude,
                'longitude' => $address->longitude,
            ]);

            // 4. Invalida alias en sesión para el Middleware
            session()->forget('user_addr_alias');
        });
    }
}