<?php

namespace App\DTOs\Admin\Driver;

use Illuminate\Http\Request;

readonly class CreateDriverData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $password,
        public string $licenseNumber,
        public string $licensePlate,
        public string $vehicleType,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            phone: $request->validated('phone'),
            password: $request->validated('password'),
            licenseNumber: $request->validated('license_number'),
            licensePlate: $request->validated('license_plate'),
            vehicleType: $request->validated('vehicle_type'),
        );
    }
}