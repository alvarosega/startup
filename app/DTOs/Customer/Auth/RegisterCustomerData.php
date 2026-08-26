<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Auth;

final readonly class RegisterCustomerData
{
    private function __construct(
        public string $phone,
        public string $email,
        public string $password,
        public string $countryCode,
        public float $latitude,
        public float $longitude,
        public ?string $branchId,
        public string $firstName,
        public string $lastName,
        public string $avatarType,
        public string $avatarSource,
        public string $alias,
        public string $address,
        public ?string $details
    ) {}

    public static function fromArray(array $validatedData): static
    {
        return new static(
            phone: (string) ($validatedData['phone'] ?? ''),
            email: (string) ($validatedData['email'] ?? ''),
            password: (string) ($validatedData['password'] ?? ''),
            countryCode: !empty($validatedData['country_code']) ? (string) $validatedData['country_code'] : 'BO',
            latitude: (float) ($validatedData['latitude'] ?? 0.0),
            longitude: (float) ($validatedData['longitude'] ?? 0.0),
            branchId: !empty($validatedData['branch_id']) ? (string) $validatedData['branch_id'] : null,
            firstName: (string) ($validatedData['first_name'] ?? ''),
            lastName: (string) ($validatedData['last_name'] ?? ''),
            avatarType: !empty($validatedData['avatar_type']) ? (string) $validatedData['avatar_type'] : 'icon',
            avatarSource: (string) ($validatedData['avatar_source'] ?? 'avatar_1.png'),
            alias: !empty($validatedData['alias']) ? (string) $validatedData['alias'] : 'Principal',
            address: (string) ($validatedData['address'] ?? ''),
            details: !empty($validatedData['details']) ? (string) $validatedData['details'] : null
        );
    }
}