<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Profiles;

final readonly class UpdateProfileData
{
    private function __construct(
        public string $firstName,
        public string $lastName,
        public string $birthDate,
        public string $gender
    ) {}

    /**
     * @param array<string, mixed> $validatedData
     */
    public static function fromArray(array $validatedData): static
    {
        return new static(
            (string) ($validatedData['first_name'] ?? ''),
            (string) ($validatedData['last_name'] ?? ''),
            (string) ($validatedData['birth_date'] ?? ''),
            (string) ($validatedData['gender'] ?? '')
        );
    }
}