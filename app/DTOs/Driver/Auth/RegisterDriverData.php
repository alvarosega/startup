<?php

declare(strict_types=1);

namespace App\DTOs\Driver\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class RegisterDriverData
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
        public ?string $avatarType,
        public string $avatarSource,
        public ?UploadedFile $ciFront,
        public ?UploadedFile $licensePhoto,
        public ?UploadedFile $vehiclePhoto
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            phone: (string) $request->input('phone'),
            email: strtolower((string) $request->input('email')),
            password: (string) $request->input('password'),
            firstName: (string) $request->input('first_name'),
            lastName: (string) $request->input('last_name'),
            licenseNumber: strtoupper((string) $request->input('license_number')),
            licensePlate: strtoupper((string) $request->input('license_plate')),
            vehicleType: (string) $request->input('vehicle_type'),
            avatarType: (string) ($request->input('avatar_type') ?? 'icon'),
            avatarSource: (string) ($request->input('avatar_source') ?? 'avatar_1.png'),
            ciFront: $request->file('ci_front'),
            licensePhoto: $request->file('license_photo'),
            vehiclePhoto: $request->file('vehicle_photo')
        );
    }
}