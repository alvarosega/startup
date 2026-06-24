<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Driver;

use App\Models\Users\Driver;
use App\DTOs\Admin\Users\Driver\StoreDriverDTO;
use App\DTOs\Admin\Users\AuditContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreDriverAction
{
    public function execute(StoreDriverDTO $dto, AuditContext $context): Driver
    {
        return DB::transaction(function () use ($dto, $context) {
            $driver = Driver::create([
                'branch_id' => $dto->branchId,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make('password'),
                'status' => $dto->status,
                'is_online' => false,
                'is_available' => false,
                'needs_password_change' => true,
                'was_previously_deleted' => false
            ]);

            $driver->profile()->create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'license_number' => $dto->licenseNumber,
                'license_plate' => $dto->licensePlate,
                'vehicle_type' => $dto->vehicleType,
                'ci_front_path' => null,
                'license_photo_path' => null,
                'vehicle_photo_path' => null
            ]);

            DB::table('audit_logs')->insert([
                'id' => (string) Str::uuid7(),
                'causer_type' => $context->causerType,
                'causer_id' => $context->causerId,
                'target_type' => Driver::class,
                'target_id' => $driver->id,
                'action' => 'driver_created_by_admin',
                'payload_before' => null,
                'payload_after' => json_encode(['email' => $dto->email, 'status' => $dto->status]),
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'created_at' => now()
            ]);

            return $driver;
        });
    }
}