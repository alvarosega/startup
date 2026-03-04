<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\DB;

class RegisterDriverAction
{
    public function execute(RegisterDriverData $data): \App\Models\Driver
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear el Driver (Padre) con seguridad Zero-Trust
            $driver = \App\Models\Driver::create([
                'phone'        => $data->phone,
                'email'        => $data->email,
                'password'     => $data->password, 
                'status'       => 'pending', // <--- CORRECCIÓN: Nace bloqueado
                'is_online'    => false,     // <--- CORRECCIÓN: Nace apagado
                'is_available' => false,     // <--- CORRECCIÓN: Nace no disponible
            ]);
    
            // 2. Crear el Detalle biográfico
            \App\Models\DriverDetail::create([
                'driver_id'      => $driver->id, 
                'first_name'     => $data->firstName,
                'last_name'      => $data->lastName,
                'license_number' => $data->licenseNumber,
                'license_plate'  => $data->licensePlate,
                'vehicle_type'   => $data->vehicleType,
                'avatar_type'    => 'icon',
                'avatar_source'  => 'avatar_1.svg',
            ]);
    
            return $driver;
        });
    }
}