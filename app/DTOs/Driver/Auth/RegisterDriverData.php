<?php

namespace App\DTOs\Driver\Auth;

use Illuminate\Http\UploadedFile;

class RegisterDriverData
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
        public string $avatarType,
        public ?string $avatarSource,
        public ?UploadedFile $avatarFile,
    ) {}
}