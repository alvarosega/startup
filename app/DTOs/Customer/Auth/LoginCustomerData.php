<?php

namespace App\DTOs\Customer\Auth;

use Illuminate\Http\Request;

readonly class LoginCustomerData
{
    public function __construct(
        public string $phone,
        public string $password,
        public bool $remember = false,
        public ?string $guestUuid = null
    ) {}


    public static function fromRequest(Request $request): self
    {
        // Usamos validated() para todo lo que esté en las reglas por seguridad
        $v = $request->validated();

        return new self(
            phone:     $v['phone'],
            password:  $v['password'],
            remember:  (bool) ($v['remember'] ?? false),
            guestUuid: $v['guest_client_uuid'] ?? null // Ya validado en el Request
        );
    }
}