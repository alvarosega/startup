<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Profiles;

final readonly class AddressData
{
    private function __construct(
        public string $alias,
        public string $address,
        public ?string $reference,
        public float $latitude,
        public float $longitude,
        public ?string $branchId,
        public bool $isDefault = false
    ) {}

    /**
     * @param array<string, mixed> $validatedData
     */
    public static function fromArray(array $validatedData): static
    {
        return new static(
            (string) ($validatedData['alias'] ?? ''),
            (string) ($validatedData['address'] ?? ''),
            isset($validatedData['reference']) ? (string) $validatedData['reference'] : null,
            (float) ($validatedData['latitude'] ?? 0.0),
            (float) ($validatedData['longitude'] ?? 0.0),
            isset($validatedData['branch_id']) ? (string) $validatedData['branch_id'] : null,
            (bool) ($validatedData['is_default'] ?? false)
        );
    }
}