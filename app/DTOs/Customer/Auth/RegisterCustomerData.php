<?php

namespace App\DTOs\Customer\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class RegisterCustomerData
{
    public function __construct(
        public string $phone,
        public string $email,
        public string $password,
        public string $firstName, // <--- AÑADIR
        public string $lastName,  // <--- AÑADIR
        public string $address,
        public ?string $alias,
        public ?string $details,
        public float $latitude,
        public float $longitude,
        public ?string $branchId, // <--- CAMBIAR A CamelCase
        public string $avatarType,
        public ?string $avatarSource,
        public ?UploadedFile $avatarFile,
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Usamos $request->input() si queremos ser menos estrictos, 
        // pero validated() es lo correcto si el paso 1 está bien.
        return new self(
            phone:         $request->validated('phone'),
            email:         $request->validated('email'),
            password:      $request->validated('password'),
            firstName:     $request->validated('first_name') ?? 'Usuario', // Fallback de seguridad
            lastName:      $request->validated('last_name') ?? '',         // Fallback de seguridad
            address:       $request->validated('address'),
            alias:         $request->validated('alias') ?? 'Casa',
            details:       $request->validated('details'),
            latitude:      (float) $request->validated('latitude'),
            longitude:     (float) $request->validated('longitude'),
            branchId:      $request->validated('branch_id'),
            avatarType:    $request->validated('avatar_type'),
            avatarSource:  $request->validated('avatar_source'),
            avatarFile:    $request->file('avatar_file'),
        );
    }
}