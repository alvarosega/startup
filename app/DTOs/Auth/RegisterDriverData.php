<?php

namespace App\DTOs\Auth;

use Illuminate\Http\UploadedFile;

class RegisterDriverData
{
    public function __construct(
        public readonly string $phone,
        public readonly string $email, // <--- FALTABA ESTE CAMPO
        public readonly string $password,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $licenseNumber,
        public readonly string $licensePlate,
        public readonly string $vehicleType,
        public readonly string $avatarType,
        public readonly ?string $avatarSource = null,
        public readonly ?UploadedFile $avatarFile = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            phone: $request->validated('phone'),
            email: $request->validated('email'), // <--- Ahora sí funcionará
            password: $request->validated('password'),
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            licenseNumber: $request->validated('license_number'),
            licensePlate: $request->validated('license_plate'),
            vehicleType: $request->validated('vehicle_type'),
            avatarType: $request->validated('avatar_type'),
            avatarSource: $request->validated('avatar_source'),
            avatarFile: $request->file('avatar_file'),
        );
    }
}