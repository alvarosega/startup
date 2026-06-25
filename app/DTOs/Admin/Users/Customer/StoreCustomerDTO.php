<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Users\Customer;

use App\Http\Requests\Admin\Users\Customer\StoreCustomerRequest;

final class StoreCustomerDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly ?string $branchId,
        public readonly bool $isActive
    ) {}

    public static function fromRequest(StoreCustomerRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            firstName: (string) $validated['first_name'],
            lastName: (string) $validated['last_name'],
            email: (string) $validated['email'],
            phone: (string) $validated['phone'],
            branchId: isset($validated['branch_id']) ? (string) $validated['branch_id'] : null,
            isActive: (bool) $validated['is_active']
        );
    }
}