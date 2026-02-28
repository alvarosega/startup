<?php

namespace App\DTOs\Admin\Driver;

use Illuminate\Http\Request;

readonly class UpdateDriverData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $licenseNumber,
        public string $licensePlate,
        public string $vehicleType,
        public bool $isIdentityVerified,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            licenseNumber: $request->validated('license_number'),
            licensePlate: $request->validated('license_plate'),
            vehicleType: $request->validated('vehicle_type'),
            isIdentityVerified: $request->boolean('is_identity_verified'),
            isActive: $request->boolean('is_active'),
        );
    }
}