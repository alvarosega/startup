<?php

namespace App\DTOs\Customer\Auth;

use Illuminate\Http\Request;

readonly class LoginData
{
    public function __construct(
        public string $phone,
        public string $password,
        public bool $remember
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            phone: $request->input('phone'),
            password: $request->input('password'),
            remember: $request->boolean('remember', false)
        );
    }
}