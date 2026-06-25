<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Users\Driver;

use App\Http\Requests\Admin\Users\Driver\StoreDriverRequest;

final class StoreDriverDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $branchId,
        public readonly string $status,
        public readonly string $licenseNumber,
        public readonly string $licensePlate,
        public readonly string $vehicleType
    ) {}

    public static function fromRequest(\App\Http\Requests\Admin\Users\Driver\StoreDriverRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            firstName: (string) $validated['first_name'],
            lastName: (string) $validated['last_name'],
            email: (string) $validated['email'],
            phone: (string) $validated['phone'],
            branchId: (string) $validated['branch_id'],
            status: (string) $validated['status'],
            licenseNumber: (string) $validated['license_number'],
            licensePlate: (string) $validated['license_plate'],
            vehicleType: (string) $validated['vehicle_type']
        );
    }
}