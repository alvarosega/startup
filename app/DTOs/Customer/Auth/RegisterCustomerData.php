<?php

namespace App\DTOs\Customer\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class RegisterCustomerData
{
    public function __construct(
        public string $phone,
        public string $countryCode,
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
        public ?string $guestUuid,
        public ?UploadedFile $avatarFile,
    ) {}

    public static function fromRequest(\App\Http\Requests\Customer\Auth\RegisterRequest $request): self
    {
        // Usamos $request->validated() para asegurar que solo pasen datos que pasaron el filtro
        $v = $request->validated();
    
        return new self(
            phone:       $v['phone'],
            countryCode: strtoupper($v['country_code']), 
            email:       $v['email'],
            password:    $v['password'],
            firstName:   $v['first_name'],
            lastName:    $v['last_name'],
            address:     $v['address'],
            alias:       $v['alias'] ?? 'Mi Ubicación',
            details:     $v['details'] ?? null,
            latitude:    (float) $v['latitude'],
            longitude:   (float) $v['longitude'],
            branchId:    $v['branch_id'] ?? null,
            avatarType:  $v['avatar_type'],
            avatarSource:$v['avatar_source'] ?? 'avatar_1.svg',
            guestUuid:    $v['guest_client_uuid'] ?? null,
            avatarFile:  $request->file('avatar_file'),
        );
    }
}