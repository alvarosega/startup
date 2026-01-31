<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class ResetPasswordData
{
    public function __construct(
        public readonly string $email,
        public readonly string $code,
        public readonly string $password
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'email'    => 'required|email|exists:users,email',
            'code'     => 'required|string|size:6', // Validamos longitud exacta
            'password' => 'required|confirmed|min:8',
        ]);

        return new self(
            email: $validated['email'],
            code: $validated['code'],
            password: $validated['password']
        );
    }
}