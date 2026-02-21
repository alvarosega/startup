<?php

namespace App\DTOs\Driver\Auth;

use Illuminate\Http\Request;

class RegisterDriverData
{
    public function __construct(
        public readonly string $phone,
        public readonly string $countryCode,
        public readonly string $email,
        public readonly string $password,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $licenseNumber,
        public readonly string $licensePlate,
        public readonly string $vehicleType,
        public readonly string $avatarType = 'icon',
        public readonly string $avatarSource = 'avatar_1.svg',
        public readonly ?\Illuminate\Http\UploadedFile $avatarFile = null,
    ) {}

    /**
     * Crea una instancia del DTO a partir del Request validado.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            phone:         $request->validated('phone'),
            countryCode:   $request->validated('country_code'),
            email:         $request->validated('email'),
            password:      $request->validated('password'),
            firstName:     $request->validated('first_name'),
            lastName:      $request->validated('last_name'),
            licenseNumber: $request->validated('license_number'),
            licensePlate:  $request->validated('license_plate'),
            vehicleType:   $request->validated('vehicle_type'),
            avatarType:    $request->input('avatar_type', 'icon'),
            avatarSource:  $request->input('avatar_source', 'avatar_1.svg'),
            avatarFile:    $request->file('avatar_file'),
        );
    }
}