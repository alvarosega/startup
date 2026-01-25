<?php

namespace App\Actions\Driver;

use App\DTOs\Driver\DriverProfileData;
use App\Models\User;
use App\Models\DriverProfile;

class UpdateDriverProfile
{
    public function execute(User $user, DriverProfileData $data): DriverProfile
    {
        // Actualizamos o creamos el perfil vinculado al usuario
        $profile = DriverProfile::updateOrCreate(
            ['user_id' => $user->id],
            $data->toArray()
        );

        return $profile;
    }
}