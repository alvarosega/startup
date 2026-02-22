<?php

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\DTOs\Customer\Profiles\AddressData;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertAddressAction
{
    public function execute(Customer $customer, AddressData $data, ?string $addressId = null): CustomerAddress
    {
        // 1. Validar límite estricto de 3 direcciones (solo para nuevos registros)
        if (!$addressId && $customer->addresses()->count() >= 3) {
            throw ValidationException::withMessages(['limit' => 'Límite de 3 direcciones alcanzado.']);
        }

        return DB::transaction(function () use ($customer, $data, $addressId) {
            $isFirst = $customer->addresses()->count() === 0;
            $shouldBeDefault = $isFirst || $data->isDefault;

            // 2. Si es default, resetear los demás
            if ($shouldBeDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }

            // 3. Crear o Actualizar (HasUuids maneja el ID automáticamente)
            $address = $customer->addresses()->updateOrCreate(
                ['id' => $addressId],
                [
                    'alias'      => $data->alias,
                    'address'    => $data->address,
                    'reference'  => $data->details,
                    'latitude'   => $data->latitude,
                    'longitude'  => $data->longitude,
                    'branch_id'  => $data->branchId,
                    'is_default' => $shouldBeDefault
                ]
            );

            // 4. Sincronizar catálogo
            if ($shouldBeDefault) {
                $customer->update(['branch_id' => $address->branch_id]);
            }

            return $address;
        });
    }
}