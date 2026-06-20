<?php

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use App\DTOs\Customer\Profiles\ProfileUpdateData;
use Illuminate\Support\Facades\DB;

class UpdatePersonalDataAction
{
    /**
     * Ejecuta la actualización de datos complementarios del cliente.
     * Solo birth_date y gender son mutables.
     */
    public function execute(Customer $customer, ProfileUpdateData $data): void
    {
        DB::transaction(function () use ($customer, $data) {
            
            // Actualizamos únicamente la tabla de perfiles
            $customer->profile()->update([
                'birth_date' => $data->birthDate,
                'gender'     => $data->gender,
            ]);

            // Nota: El email y el teléfono son inmutables por diseño de seguridad.
            // Si en el futuro se requiere cambiar el email, se debe crear un Action 
            // separado con protocolo de verificación (OTP).
        });
    }
}