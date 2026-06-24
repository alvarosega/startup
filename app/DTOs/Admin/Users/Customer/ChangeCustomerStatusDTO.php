<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Users\Customer;

use App\Http\Requests\Admin\Users\Customer\ChangeCustomerStatusRequest;

final class ChangeCustomerStatusDTO
{
    public function __construct(
        public readonly string $customerId,
        public readonly bool $isActive
    ) {}

    public static function fromRequest(ChangeCustomerStatusRequest $request, string $customerId): self
    {
        return new self(
            customerId: $customerId,
            isActive: (bool) $request->validated('is_active')
        );
    }
}