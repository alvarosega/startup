<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Auth;

use App\Http\Requests\Admin\Auth\LoginAdminRequest;

readonly class LoginAdminData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
        public string $ip,
        public string $userAgent,
    ) {}

    /**
     * Factoría estática acoplada de forma segura al FormRequest validado.
     */
    public static function fromRequest(LoginAdminRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            email: (string) $validated['email'],
            password: (string) $validated['password'],
            remember: (bool) $request->boolean('remember'),
            ip: (string) ($request->ip() ?? '127.0.0.1'),
            userAgent: (string) ($request->userAgent() ?? 'Unknown')
        );
    }
}