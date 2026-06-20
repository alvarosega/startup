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
            // 1. Localizar o Instanciar
            $driver = Driver::findOrFail($dto->id);
    
            // 2. Actualizar Entidad de Autenticación
            $driver->update([
                'phone'     => $dto->phone,
                'email'     => $dto->email,
                'branch_id' => $dto->branchId,
                'status'    => $dto->status,
            ]);
    
            if ($dto->password) {
                $driver->update(['password' => Hash::make($dto->password)]);
            }
    
            // 3. Actualizar Perfil (Relación uno a uno)
            $driver->profile()->update([
                'first_name'       => $dto->firstName,
                'last_name'        => $dto->lastName,
                'license_number'   => $dto->licenseNumber,
                'license_plate'    => $dto->licensePlate,
                'vehicle_type'     => $dto->vehicleType,
                'rejection_reason' => $dto->status === 'suspended' ? $dto->rejectionReason : null,
            ]);
    
            return $driver;
        });
    }
}