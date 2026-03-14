<?php

namespace App\Actions\Customer\Profiles;

use App\Models\{Customer, CustomerAddress};
use Illuminate\Support\Facades\DB;

class DeleteAddressAction
{
    public function execute(Customer $customer, string $addressId): void
    {
        $address = $customer->addresses()->findOrFail($addressId);

        DB::transaction(function () use ($customer, $address) {
            $wasDefault = $address->is_default;
            
            $address->delete();

            // Si borramos la principal, debemos promover otra automáticamente
            if ($wasDefault) {
                $newDefault = $customer->addresses()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                    
                    // Replicar los datos de la nueva dirección promovida
                    $customer->update([
                        'branch_id' => $newDefault->branch_id,
                        'latitude'  => $newDefault->latitude,
                        'longitude' => $newDefault->longitude,
                    ]);
                } else {
                    // Si ya no quedan direcciones, limpiamos el contexto geográfico
                    $customer->update([
                        'branch_id' => null,
                        'latitude'  => null,
                        'longitude' => null,
                    ]);
                }
            }
        });
    }
}