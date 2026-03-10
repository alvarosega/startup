<?php

namespace App\DTOs\Customer\Auth;

use App\Http\Requests\Customer\Auth\ResetPasswordRequest;

readonly class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $code,
        public string $password
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        $v = $request->validated();
        return new self(
            email:    $v['email'],
            code:     $v['code'],
            password: $v['password']
        );
    }
}