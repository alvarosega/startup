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
            
            // LÓGICA DE MAPEO DE ESTADO
            // 1. Si el admin desactiva la cuenta -> 'inactive' (prioridad)
            // 2. Si la cuenta está activa pero la identidad no está verificada -> 'pending'
            // 3. Si la cuenta está activa e identidad verificada -> 'active'
            
            $finalStatus = 'inactive';
            
            if ($data->isActive) {
                $finalStatus = $data->isIdentityVerified ? 'active' : 'pending';
            }

            // 1. Actualización de la tabla principal 'drivers'
            $driver->update([
                'branch_id' => $data->branchId,
                'status'    => $finalStatus,
            ]);

            // 2. Actualización de 'driver_details' (Sin columnas eliminadas)
            $driver->details()->update([
                'first_name'     => $data->firstName,
                'last_name'      => $data->lastName,
                'license_number' => $data->licenseNumber,
                'license_plate'  => $data->licensePlate,
                'vehicle_type'   => $data->vehicleType,
                // YA NO EXISTE 'verification_status' AQUÍ
            ]);
        });
    }
}