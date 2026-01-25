<?php

namespace App\DTOs\Auth;

use Illuminate\Http\UploadedFile;

class RegisterData
{
    public function __construct(
        public readonly string $phone,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role, // 'client' o 'driver'
        // Avatar
        public readonly string $avatarType,
        public readonly ?string $avatarSource = null,
        public readonly ?UploadedFile $avatarFile = null,
        // Ubicación
        public readonly ?string $alias = null,
        public readonly ?string $address = null,
        public readonly ?string $details = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?int $branchId = null,
        // Auditoría
        public readonly ?string $guestSessionId = null
    ) {}

    public static function fromRequest($request): self
    {
        // La limpieza del teléfono ya la hará el FormRequest antes de llegar aquí,
        // pero por seguridad podríamos replicarla o confiar en el Request validado.
        return new self(
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            role: $request->validated('role'),
            avatarType: $request->validated('avatar_type'),
            avatarSource: $request->validated('avatar_source'),
            avatarFile: $request->file('avatar_file'),
            alias: $request->validated('alias'),
            address: $request->validated('address'),
            details: $request->validated('details'),
            latitude: $request->float('latitude'), // Helper float() de Laravel
            longitude: $request->float('longitude'),
            branchId: $request->validated('branch_id'),
            guestSessionId: $request->session()->getId()
        );
    }
}