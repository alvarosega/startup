<?php

namespace App\DTOs\Driver\Auth;

use App\Http\Requests\Driver\Auth\ResetPasswordRequest;

readonly class ResetPasswordData
{
    public function __construct(
        public string $email,
        public string $code,
        public string $password,
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            code: $request->validated('code'),
            password: $request->validated('password'),
        );
    }
}