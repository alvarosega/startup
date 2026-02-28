<?php

namespace App\Actions\Admin\Driver;

use App\DTOs\Admin\Driver\CreateDriverData;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDriverAction
{
    public function execute(CreateDriverData $data): Driver
    {
        return DB::transaction(function () use ($data) {
            $driver = Driver::create([
                'phone'        => $data->phone,
                'email'        => 'driver_' . Str::random(8) . '@system.local',
                'password'     => $data->password,
                // CORRECCIÓN: Usar la columna status real
                'status'       => 'active', 
                'is_online'    => false,
                'is_available' => false,
            ]);

            $driver->details()->create([
                'first_name'          => $data->firstName,
                'last_name'           => $data->lastName,
                'license_number'      => $data->licenseNumber,
                'license_plate'       => $data->licensePlate,
                'vehicle_type'        => $data->vehicleType,
                'verification_status' => 'verified',
            ]);

            return $driver;
        });
    }
}