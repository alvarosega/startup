<?php

namespace App\DTOs\Driver\Profile;

class DriverProfileData
{
    public function __construct(
        public readonly string $licenseNumber,
        public readonly string $licensePlate,
        public readonly string $vehicleType,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            licenseNumber: $request->validated('license_number'),
            // Forzamos mayúsculas en la placa para estandarizar
            licensePlate: strtoupper($request->validated('license_plate')),
            vehicleType: $request->validated('vehicle_type'),
        );
    }

    public function toArray(): array
    {
        return [
            'license_number' => $this->licenseNumber,
            'license_plate' => $this->licensePlate,
            'vehicle_type' => $this->vehicleType,
        ];
    }
}