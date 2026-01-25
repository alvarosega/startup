<?php

namespace App\DTOs\Profile;

class UpdateProfileData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        // El teléfono no se edita aquí porque es el ID de login, requiere otro proceso
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            email: $request->validated('email'),
            birthDate: $request->validated('birth_date'),
            gender: $request->validated('gender'),
        );
    }
}