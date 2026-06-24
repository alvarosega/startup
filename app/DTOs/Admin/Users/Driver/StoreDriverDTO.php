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

    public static function fromRequest(StoreDriverRequest $request): self
    {
        return new self(
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            branchId: $request->validated('branch_id'),
            status: $request->validated('status'),
            licenseNumber: $request->validated('license_number'),
            licensePlate: $request->validated('license_plate'),
            vehicleType: $request->validated('vehicle_type')
        );
    }
}