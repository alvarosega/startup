<?php

namespace App\DTOs\Admin\Users\Customer;

use App\Http\Requests\Admin\Users\Customer\UpsertCustomerRequest;

readonly class UpsertCustomerDTO
{
    public function __construct(
        public ?string $id, // Nulo = Crear, String = Actualizar
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public ?string $password,
        public ?string $branchId,
        public bool $isActive,
        // Ubicación (Solo requerida en Creación)
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
    ) {}

    public static function fromRequest(UpsertCustomerRequest $request, ?string $id = null): self
    {
        $v = $request->validated();
        
        return new self(
            id:        $id,
            firstName: $v['first_name'],
            lastName:  $v['last_name'],
            email:     $v['email'],
            phone:     $v['phone'],
            password:  $v['password'] ?? null,
            branchId:  $v['branch_id'] ?? null,
            isActive:  (bool) ($v['is_active'] ?? true), // True por defecto al crear
            address:   $v['address'] ?? null,
            latitude:  isset($v['latitude']) ? (float) $v['latitude'] : null,
            longitude: isset($v['longitude']) ? (float) $v['longitude'] : null,
        );
    }
}