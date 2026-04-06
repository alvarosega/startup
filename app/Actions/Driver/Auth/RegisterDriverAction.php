<?php

declare(strict_types=1);

namespace App\Actions\Driver\Auth;

use App\Models\Driver;
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterDriverAction
{

    public function execute(RegisterDriverData $data): Driver
    {
        return DB::transaction(function () use ($data) {
            
            $driver = Driver::create([
                'phone'     => $data->phone,
                'email'     => $data->email,
                'password'  => Hash::make($data->password),
                'status'    => 'pending', 
                'branch_id' => null, 
            ]);

            $ciPath = $data->ciFront 
                ? $data->ciFront->store("drivers/{$driver->id}/documents", 'private') 
                : null;
                
            $licensePath = $data->licensePhoto 
                ? $data->licensePhoto->store("drivers/{$driver->id}/documents", 'private') 
                : null;

            $driver->profile()->create([
                'first_name'         => $data->firstName,
                'last_name'          => $data->lastName,
                'license_number'     => $data->licenseNumber,
                'license_plate'      => $data->licensePlate,
                'vehicle_type'       => $data->vehicleType,
                'ci_front_path'      => $ciPath,
                'license_photo_path' => $licensePath, // RECTIFICADO: Coincide con migración
                'avatar_type'        => $data->avatarType,
                'avatar_source'      => $data->avatarSource,
            ]);

            $driver->assignRole('driver');

            return $driver;
        });
    }
}