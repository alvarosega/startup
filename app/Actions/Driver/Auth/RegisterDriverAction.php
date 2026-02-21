<?php

namespace App\Actions\Driver\Auth;

use App\Models\Driver;
use App\Models\DriverDetail;
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\{DB, Hash, Log};
use Illuminate\Support\Str;

class RegisterDriverAction
{
    

    public function execute(RegisterDriverData $data): Driver
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear el Driver (HasUuids generará el string automáticamente)
            $driver = Driver::create([
                'phone'    => $data->phone,
                'email'    => $data->email,
                'password' => $data->password, 
                'country_code' => $data->countryCode,
                'status'   => 'pending',
            ]);

            // 2. Crear los detalles vinculados
            $driver->details()->create([
                'first_name'     => $data->firstName,
                'last_name'      => $data->lastName,
                'license_number' => $data->licenseNumber,
                'license_plate'  => $data->licensePlate,
                'vehicle_type'   => $data->vehicleType,
                'avatar_type'    => $data->avatarType,
                'avatar_source'  => $data->avatarSource,
            ]);

            // 3. Asignar rol (Spatie)
            $driver->assignRole('driver');

            return $driver;
        });
    }
}