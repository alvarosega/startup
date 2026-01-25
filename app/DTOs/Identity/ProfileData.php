<?php

namespace App\DTOs\Identity;

class ProfileData
{
    public function __construct(
        public readonly ?string $email,       // Tabla users
        public readonly ?string $firstName,   // Tabla user_profiles
        public readonly ?string $lastName,    // Tabla user_profiles
        public readonly ?string $birthDate,   // Tabla user_profiles
        public readonly ?string $gender,      // Tabla user_profiles
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->validated('email'),
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            birthDate: $request->validated('birth_date'),
            gender: $request->validated('gender'),
        );
    }

    public function toArrayForProfile(): array
    {
        // Solo devolvemos lo que va a la tabla user_profiles
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'birth_date' => $this->birthDate,
            'gender' => $this->gender,
        ];
    }
}