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
                'branch_id' => $data->branchId,
                'phone'     => $data->phone,
                'email'     => 'driver_' . Str::random(8) . '@system.local',
                'password'  => $data->password,
                'status'    => 'active', // Al crearlo el admin, nace activo
                'is_online' => false,
            ]);

            $driver->details()->create([
                'first_name'     => $data->firstName,
                'last_name'      => $data->lastName,
                'license_number' => $data->licenseNumber,
                'license_plate'  => $data->licensePlate,
                'vehicle_type'   => $data->vehicleType,
                // ELIMINADO: 'verification_status'
            ]);

            return $driver;
        });
    }
}