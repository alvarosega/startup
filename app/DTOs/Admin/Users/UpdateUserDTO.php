<?php

namespace App\DTOs\Admin\User;

use App\Http\Requests\Admin\Users\UpdateUserRequest;

readonly class UpdateUserDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public ?string $branchId,
        public bool $isActive,
        public ?string $password = null,
    ) {}

    public static function fromRequest(UpdateUserRequest $request): self
    {
        $v = $request->validated();

        return new self(
            firstName: $v['first_name'],
            lastName:  $v['last_name'],
            email:     $v['email'],
            phone:     $v['phone'],
            branchId:  $v['branch_id'] ?? null,
            isActive:  (bool) ($v['is_active'] ?? true),
            password:  $v['password'] ?? null,
        );
    }
}