<?php

namespace App\Actions\Driver\Auth;

use App\Models\Driver;
use App\Models\DriverDetail;
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RegisterDriverAction
{
    public function execute(RegisterDriverData $data): Driver
    {
        return DB::transaction(function () use ($data) {
            // Generar UUID y convertir a binario
            $uuid = Str::uuid()->toString();
            $binaryId = hex2bin(str_replace('-', '', $uuid));
    
            Log::info('Registrando Driver', ['uuid' => $uuid]);
    
            $driver = Driver::create([
                'id'       => $binaryId,
                'phone'    => $data->phone,
                'email'    => $data->email,
                'password' => Hash::make($data->password),
                'status'   => 'pending',
            ]);
    
            DriverDetail::create([
                'driver_id'      => $binaryId,
                'first_name'     => $data->firstName,
                'last_name'      => $data->lastName,
                'license_number' => $data->licenseNumber,
                'license_plate'  => $data->licensePlate,
                'vehicle_type'   => $data->vehicleType,
            ]);
    
            return $driver->fresh(); // fresh() recarga el modelo desde la DB con los casts aplicados
        });
    }
}