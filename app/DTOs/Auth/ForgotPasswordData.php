<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class ForgotPasswordData
{
    public function __construct(
        public readonly string $email
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->validate(['email' => 'required|email|exists:users,email'])['email']
        );
    }
}