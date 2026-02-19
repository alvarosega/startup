<?php

namespace App\DTOs\Driver\Auth;

use Illuminate\Http\Request;

readonly class LoginDriverData
{
    public function __construct(
        public string $phone,
        public string $password,
        public bool $remember = false
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            phone:    $request->validated('phone'),
            password: $request->validated('password'),
            remember: (bool) $request->validated('remember', false)
        );
    }
}