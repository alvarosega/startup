<?php

namespace App\DTOs\Customer\Auth;

use App\Http\Requests\Customer\Auth\RegisterRequest;

readonly class RegisterCustomerData
{
    public function __construct(
        public string $phone,
        public string $countryCode,
        public string $email,
        public string $password,
        public string $firstName,
        public string $lastName,
        public string $address,
        public string $alias,
        public ?string $details,
        public float $latitude,
        public float $longitude,
        public ?string $branchId,
        public ?string $guestUuid,
        public string $avatarType,
        public string $avatarSource,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        $v = $request->validated();
    
        return new self(
            phone:        (string) $v['phone'],
            countryCode:  (string) $v['country_code'],
            email:        (string) $v['email'],
            password:     (string) $v['password'],
            firstName:    (string) $v['first_name'],
            lastName:     (string) $v['last_name'],
            address:      (string) $v['address'],
            alias:        (string) ($v['alias'] ?? 'Casa'),
            details:      $v['details'] ?? null,
            latitude:     (float) $v['latitude'],
            longitude:    (float) $v['longitude'],
            branchId:     $v['branch_id'] ?? null,
            guestUuid:    $v['guest_client_uuid'] ?? null,
            avatarType:   (string) $v['avatar_type'],
            avatarSource: (string) $v['avatar_source'],
            // Dictamen: Se elimina el parámetro avatarFile para cumplir con el protocolo de inmutabilidad visual.
        );
    }
}