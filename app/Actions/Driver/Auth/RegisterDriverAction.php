<?php

declare(strict_types=1);

namespace App\Actions\Driver\Auth;

use App\Models\Users\Driver;
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegisterDriverAction
{
    public function execute(RegisterDriverData $data): Driver
    {
        return DB::transaction(function () use ($data): Driver {
            $driverId = (string) Str::uuid7();

            /** @var Driver $driver */
            $driver = Driver::query()->create([
                'id'                     => $driverId,
                'phone'                  => $data->phone,
                'email'                  => $data->email,
                'password'               => $data->password, // Hasheado automático por cast
                'status'                 => 'pending',
                'branch_id'              => null,
                'is_online'              => false,
                'is_available'           => false,
                'was_previously_deleted' => false,
                'needs_password_change'  => false,
                'deleted_epoch'          => 0,
            ]);

            // Almacenamiento seguro en disco local no público
            $ciPath = $data->ciFront
                ? $data->ciFront->store("drivers/{$driverId}/documents", 'local')
                : null;

            $licensePath = $data->licensePhoto
                ? $data->licensePhoto->store("drivers/{$driverId}/documents", 'local')
                : null;

            $vehiclePath = $data->vehiclePhoto
                ? $data->vehiclePhoto->store("drivers/{$driverId}/documents", 'local')
                : null;

            $driver->profile()->create([
                'first_name'         => mb_convert_encoding(mb_strtoupper($data->firstName), 'UTF-8'),
                'last_name'          => mb_convert_encoding(mb_strtoupper($data->lastName), 'UTF-8'),
                'license_number'     => $data->licenseNumber,
                'license_plate'      => $data->licensePlate,
                'vehicle_type'       => $data->vehicleType,
                'ci_front_path'      => $ciPath,
                'license_photo_path' => $licensePath,
                'vehicle_photo_path' => $vehiclePath,
                'avatar_type'        => $data->avatarType ?? 'icon',
                'avatar_source'      => $data->avatarSource,
            ]);

            $driver->assignRole('driver');

            return $driver;
        });
    }
}