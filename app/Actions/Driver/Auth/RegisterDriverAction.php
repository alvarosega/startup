<?php

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Models\Driver;
use App\Models\DriverProfile; // <--- Cambio de modelo
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterDriverAction
{
    public function execute(RegisterDriverData $data): Driver
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear el Driver (Entidad de Autenticación)
            $driver = Driver::create([
                'phone'        => $data->phone,
                'email'        => $data->email,
                'password'     => Hash::make($data->password),
                'status'       => 'pending', 
                'is_online'    => false,     
                'is_available' => false,     
            ]);
    
            // 2. Crear el Perfil (Entidad Biográfica y Documental)
            DriverProfile::create([
                'driver_id'      => $driver->id, // <--- FK correcta
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