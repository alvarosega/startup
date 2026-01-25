<?php

namespace App\DTOs\User;

class UserData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $phone,
        public readonly ?string $email,
        public readonly ?string $password, // Null en update si no cambia
        public readonly int $roleId,
        public readonly ?int $branchId,
        public readonly bool $isActive
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            roleId: (int) $request->validated('role_id'),
            branchId: $request->validated('branch_id'),
            isActive: $request->boolean('is_active', true) // Default true en create
        );
    }
}