<?php

namespace App\DTOs\Driver\Auth;

use App\Http\Requests\Driver\Auth\LoginRequest;

readonly class LoginDriverData
{
    public function __construct(
        public string $phone,
        public string $password,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            phone: $request->validated('phone'),
            password: $request->validated('password'),
        );
    }
}