<?php

namespace App\Actions\Admin\Driver;

use App\Models\Driver;
use App\DTOs\Admin\Driver\UpsertDriverDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpsertDriverAction
{
    public function execute(UpsertDriverDTO $dto): Driver
    {
        return DB::transaction(function () use ($dto) {
            $isNew = empty($dto->id);
            $driverId = $dto->id ?? Str::uuid()->toString();

            // Lógica de Máquina de Estados (Rechazo vs Aprobación)
            $finalStatus = 'inactive';
            if ($dto->isActive) {
                if ($dto->rejectionReason) {
                    $finalStatus = 'rejected';
                } else {
                    $finalStatus = $dto->isIdentityVerified ? 'active' : 'pending';
                }
            }

            // 1. Data Core
            $driverData = [
                'phone'     => $dto->phone,
                'email'     => $dto->email,
                'branch_id' => $dto->branchId,
                'status'    => $finalStatus,
            ];

            if ($dto->password) {
                $driverData['password'] = Hash::make($dto->password);
            }

            // Defaults de nacimiento
            if ($isNew) {
                $driverData['is_online'] = false;
                $driverData['is_available'] = false;
            }

            $driver = Driver::updateOrCreate(['id' => $driverId], $driverData);

            // 2. Data Detalle Documental
            $driver->details()->updateOrCreate(
                ['driver_id' => $driver->id],
                [
                    'first_name'       => $dto->firstName,
                    'last_name'        => $dto->lastName,
                    'license_number'   => $dto->licenseNumber,
                    'license_plate'    => $dto->licensePlate,
                    'vehicle_type'     => $dto->vehicleType,
                    'rejection_reason' => $finalStatus === 'rejected' ? $dto->rejectionReason : null,
                ]
            );

            return $driver;
        });
    }
}