<?php

namespace App\DTOs\Admin\User;

use App\Http\Requests\Admin\User\StoreUserRequest;

readonly class StoreUserDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public string $password,
        public ?string $branchId,
        public string $address,
        public float $latitude,
        public float $longitude,
    ) {}

    public static function fromRequest(StoreUserRequest $request): self
    {
        $v = $request->validated();
        return new self(
            firstName: $v['first_name'],
            lastName:  $v['last_name'],
            email:     $v['email'],
            phone:     $v['phone'],
            password:  $v['password'],
            branchId:  $v['branch_id'] ?? null,
            address:   $v['address'],
            latitude:  (float) $v['latitude'],
            longitude: (float) $v['longitude'],
        );
    }
}