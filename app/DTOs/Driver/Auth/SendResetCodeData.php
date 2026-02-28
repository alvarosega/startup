<?php

namespace App\DTOs\Driver\Auth;

use App\Http\Requests\Driver\Auth\ForgotPasswordRequest;

readonly class SendResetCodeData
{
    public function __construct(
        public string $email,
    ) {}

    public static function fromRequest(ForgotPasswordRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
        );
    }
}