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
        return new self(
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            branchId: $request->validated('branch_id'),
            isActive: (bool) $request->validated('is_active')
        );
    }
}