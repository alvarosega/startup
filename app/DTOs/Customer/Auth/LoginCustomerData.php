<?php

namespace App\DTOs\Customer\Auth;

use Illuminate\Http\Request;

readonly class LoginCustomerData
{
    public function __construct(
        public string $phone,
        public string $password,
        public bool $remember
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Asumimos que el Request ya validó y formateó los datos
        return new self(
            phone: $request->input('phone'),
            password: $request->input('password'),
            remember: $request->boolean('remember')
        );
    }
}