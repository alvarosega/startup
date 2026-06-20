<?php

namespace App\DTOs\Customer\Auth;

use App\Http\Requests\Customer\Auth\LoginRequest;

readonly class LoginCustomerData
{
    public function __construct(
        public string $phone,
        public string $password,
        public bool $remember = false,
        public ?string $guestUuid = null
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        // Dictamen: Usamos solo datos validados para evitar inyección de parámetros sucios
        $v = $request->validated();

        return new self(
            phone:     (string) $v['phone'],
            password:  (string) $v['password'],
            remember:  (bool) ($v['remember'] ?? false),
            guestUuid: $v['guest_client_uuid'] ?? null
        );
    }
}