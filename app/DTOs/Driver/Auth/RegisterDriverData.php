<?php

namespace App\DTOs\Driver\Auth;

use App\Http\Requests\Driver\Auth\RegisterRequest;

readonly class RegisterDriverData
{
    public function __construct(
        public string $phone,
        public string $email,
        public string $password,
        public string $firstName,
        public string $lastName,
        public string $licenseNumber,
        public string $licensePlate,
        public string $vehicleType,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            phone:         $request->validated('phone'),
            email:         $request->validated('email'),
            password:      $request->validated('password'),
            firstName:     $request->validated('first_name'),
            lastName:      $request->validated('last_name'),
            licenseNumber: $request->validated('license_number'),
            licensePlate:  $request->validated('license_plate'),
            vehicleType:   $request->validated('vehicle_type'),
        );
    }
}