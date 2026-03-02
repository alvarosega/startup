<?php

namespace App\Actions\Admin\Driver;

use App\DTOs\Admin\Driver\UpdateDriverData;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class UpdateDriverAction
{
    public function execute(Driver $driver, UpdateDriverData $data): void
    {
        DB::transaction(function () use ($driver, $data) {
            
            // 1. Actualización de la tabla principal 'drivers'
            $driver->update([
                'branch_id' => $data->branchId, // <-- CORRECCIÓN: Pertenece a esta tabla
                'status'    => $data->isActive ? 'active' : 'inactive',
            ]);

            // 2. Actualización de la tabla relacionada 'driver_details'
            $driver->details()->update([
                'first_name'          => $data->firstName,
                'last_name'           => $data->lastName,
                'license_number'      => $data->licenseNumber,
                'license_plate'       => $data->licensePlate,
                'vehicle_type'        => $data->vehicleType,
                'verification_status' => $data->isIdentityVerified ? 'verified' : 'pending',
            ]);
        });
    }
}