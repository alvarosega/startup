<?php

namespace App\DTOs\Admin\Auth;

use Illuminate\Http\Request;

readonly class LoginAdminData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
        public string $ip,
        public string $userAgent,
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Corrección: Acceso correcto al mapa de datos validados
        $validated = $request->validated();

        return new self(
            email: $validated['email'],
            password: $validated['password'],
            remember: $request->boolean('remember'),
            ip: $request->ip(),
            userAgent: $request->userAgent() ?? 'Unknown'
        );
    }
}