<?php

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use App\DTOs\Customer\Profiles\ProfileUpdateData;
use Illuminate\Support\Facades\DB;

class UpdatePersonalDataAction
{
    public function execute(Customer $customer, ProfileUpdateData $data): void
    {
        DB::transaction(function () use ($customer, $data) {
            // 1. Actualizar cuenta principal
            $customer->update(['email' => $data->email]);

            // 2. Actualizar o crear perfil extendido
            $customer->profile()->updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'first_name' => $data->firstName,
                    'last_name'  => $data->lastName,
                    'birth_date' => $data->birthDate,
                    'gender'     => $data->gender,
                ]
            );
        });
    }
}