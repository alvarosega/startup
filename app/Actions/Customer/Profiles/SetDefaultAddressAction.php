<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Models\Users\CustomerAddress;
use Illuminate\Support\Facades\DB;

final class SetDefaultAddressAction
{
    public function execute(string $customerId, string $addressId): void
    {
        DB::transaction(function () use ($customerId, $addressId): void {
            // Desmarcar la dirección predeterminada previa
            CustomerAddress::query()
                ->where('customer_id', $customerId)
                ->where('is_default', true)
                ->where('deleted_epoch', 0)
                ->update(['is_default' => false]);

            // Asignar preferencia a la nueva dirección seleccionada
            /** @var CustomerAddress $address */
            $address = CustomerAddress::query()
                ->where('id', $addressId)
                ->where('customer_id', $customerId)
                ->where('deleted_epoch', 0)
                ->findOrFail($addressId);

            $address->update(['is_default' => true]);

            // Sincronización estricta con la tabla core Customers (Regla de negocio 3)
            DB::table('customers')
                ->where('id', $customerId)
                ->update(['branch_id' => $address->branch_id]);
        });
    }
}