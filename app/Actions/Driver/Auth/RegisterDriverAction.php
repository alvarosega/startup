<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\DB;

class RegisterDriverAction
{
    /**
     * Registro con Namespaces Absolutos para evadir corrupciones de autoloader.
     */
    public function execute(RegisterDriverData $data): \App\Models\Driver
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Creación del Driver (Autenticación)
            // Eliminado 'is_active' que no existe en tu migración.
            $driver = \App\Models\Driver::create([
                'phone'    => $data->phone,
                'email'    => $data->email,
                'password' => $data->password, 
                'status'   => 'active', // Este es el campo real de tu DB
            ]);

            // 2. Creación del Detalle (Perfil)
            $driver->details()->create([
                'first_name'          => $data->firstName,
                'last_name'           => $data->lastName,
                'license_number'      => $data->licenseNumber,
                'license_plate'       => $data->licensePlate,
                'vehicle_type'        => $data->vehicleType,
                'verification_status' => 'pending', 
                'avatar_type'         => 'icon',
                'avatar_source'       => 'avatar_1.svg',
            ]);

            return $driver;
        });
    }
}