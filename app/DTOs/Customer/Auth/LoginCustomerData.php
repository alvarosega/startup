<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Auth;

final readonly class LoginCustomerData
{
    private function __construct(
        public string $phone,
        public string $password,
        public bool $remember,
        public ?string $guestUuid
    ) {}

    /**
     * @param array<string, mixed> $validatedData
     */
    public static function fromArray(array $validatedData): static
    {
        return new static(
            (string) ($validatedData['phone'] ?? ''),
            (string) ($validatedData['password'] ?? ''),
            (bool) ($validatedData['remember'] ?? false),
            isset($validatedData['guest_client_uuid']) ? (string) $validatedData['guest_client_uuid'] : null
        );
    }
}