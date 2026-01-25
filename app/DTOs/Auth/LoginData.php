<?php
namespace App\DTOs\Auth;

class LoginData
{
    public function __construct(
        public readonly string $phone, // <-- CAMBIO: Email por Phone
        public readonly string $password,
        public readonly string $deviceName,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            phone: $request->validated('phone'),
            password: $request->validated('password'),
            deviceName: $request->validated('device_name', 'Web Browser'), // Default para web
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );
    }
}