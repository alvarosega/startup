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
        public ?string $details,
        public ?string $guestUuid
    ) {}

    /**
     * @param array<string, mixed> $validatedData
     */
    public static function fromArray(array $validatedData): static
    {
        return new static(
            (string) ($validatedData['phone'] ?? ''),
            (string) ($validatedData['email'] ?? ''),
            (string) ($validatedData['password'] ?? ''),
            (string) ($validatedData['country_code'] ?? 'BO'),
            (float) ($validatedData['latitude'] ?? 0.0),
            (float) ($validatedData['longitude'] ?? 0.0),
            isset($validatedData['branch_id']) ? (string) $validatedData['branch_id'] : null,
            (string) ($validatedData['first_name'] ?? ''),
            (string) ($validatedData['last_name'] ?? ''),
            (string) ($validatedData['avatar_type'] ?? 'icon'),
            (string) ($validatedData['avatar_source'] ?? 'avatar_1.svg'),
            (string) ($validatedData['alias'] ?? 'Casa'),
            (string) ($validatedData['address'] ?? ''),
            isset($validatedData['details']) ? (string) $validatedData['details'] : null,
            isset($validatedData['guest_client_uuid']) ? (string) $validatedData['guest_client_uuid'] : null
        );
    }
}