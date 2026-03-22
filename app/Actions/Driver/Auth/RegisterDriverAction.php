<?php

namespace App\Actions\Driver\Auth;

use App\Models\{Driver, DriverProfile};
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\{DB, Hash, Storage};

class RegisterDriverAction
{
    public function execute(RegisterDriverData $data): Driver
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Entidad de Autenticación
            $driver = Driver::create([
                'phone'    => $data->phone,
                'email'    => $data->email,
                'password' => Hash::make($data->password),
                'status'   => 'pending', // Espera de validación Admin
                'branch_id'=> null,      // Se asigna en la aprobación
            ]);

            // 2. Persistencia de Documentos en Disco Privado
            $ciPath = $data->ciFront 
                ? $data->ciFront->store("drivers/{$driver->id}/documents", 'private') 
                : null;
                
            $licensePath = $data->licensePhoto 
                ? $data->licensePhoto->store("drivers/{$driver->id}/documents", 'private') 
                : null;

            // 3. Entidad Biográfica y Legal
            $driver->profile()->create([
                'first_name'      => $data->firstName,
                'last_name'       => $data->lastName,
                'license_number'  => $data->licenseNumber,
                'license_plate'   => $data->licensePlate,
                'vehicle_type'    => $data->vehicleType,
                'ci_front_path'   => $ciPath,
                'license_path'    => $licensePath,
                'avatar_type'     => 'icon',
                'avatar_source'   => 'avatar_1.svg',
            ]);

            $driver->assignRole('driver');

            return $driver;
        });
    }
}