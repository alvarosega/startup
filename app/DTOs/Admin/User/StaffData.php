<?php

declare(strict_types=1);

namespace App\DTOs\Admin\User;

use App\Http\Requests\Admin\Users\Staff\StoreStaffRequest;
use App\Http\Requests\Admin\Users\Staff\UpdateStaffRequest;

final readonly class StaffData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $phone,
        public string $email,
        public ?string $password,
        public ?string $branchId,
        public bool $isActive,
        /** @var array<string> */
        public array $roles,
        /** @var array<string> */
        public array $permissions = []
    ) {}

    public static function fromStoreRequest(StoreStaffRequest $request): self
    {
        $validated = $request->validated();
        $roles = (array) ($validated['roles'] ?? []);
        $isSuperAdmin = in_array('super_admin', $roles, true);

        return new self(
            firstName: (string) $validated['first_name'],
            lastName: (string) $validated['last_name'],
            phone: !empty($validated['phone']) ? (string) $validated['phone'] : null,
            email: strtolower(trim((string) $validated['email'])),
            password: (string) $validated['password'],
            branchId: !$isSuperAdmin && !empty($validated['branch_id']) ? (string) $validated['branch_id'] : null,
            isActive: (bool) ($validated['is_active'] ?? true),
            roles: $roles,
            permissions: (array) ($validated['permissions'] ?? [])
        );
    }

    public static function fromUpdateRequest(UpdateStaffRequest $request): self
    {
        $validated = $request->validated();
        $roles = (array) ($validated['roles'] ?? []);
        $isSuperAdmin = in_array('super_admin', $roles, true);

        return new self(
            firstName: (string) $validated['first_name'],
            lastName: (string) $validated['last_name'],
            phone: !empty($validated['phone']) ? (string) $validated['phone'] : null,
            email: strtolower(trim((string) $validated['email'])),
            password: !empty($validated['password']) ? (string) $validated['password'] : null,
            branchId: !$isSuperAdmin && !empty($validated['branch_id']) ? (string) $validated['branch_id'] : null,
            isActive: (bool) ($validated['is_active'] ?? true),
            roles: $roles,
            permissions: (array) ($validated['permissions'] ?? [])
        );
    }
}
