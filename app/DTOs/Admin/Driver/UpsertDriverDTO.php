<?php

namespace App\DTOs\Admin\Driver;

use App\Http\Requests\Admin\Driver\UpsertDriverRequest;

readonly class UpsertDriverDTO
{
    public function __construct(
        public ?string $id,
        public ?string $branchId,
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $email,
        public ?string $password,
        public string $licenseNumber,
        public string $licensePlate,
        public string $vehicleType,
        public string $status, // <--- CAMBIO: Usar el string directamente
        public ?string $rejectionReason,
    ) {}

    public static function fromRequest(UpsertDriverRequest $request, ?string $id = null): self
    {
        $v = $request->validated();
        
        return new self(
            id:              $id,
            branchId:        $v['branch_id'] ?? null,
            firstName:       $v['first_name'],
            lastName:        $v['last_name'],
            phone:           $v['phone'],
            email:           $v['email'],
            password:        $v['password'] ?? null,
            licenseNumber:   $v['license_number'],
            licensePlate:    $v['license_plate'],
            vehicleType:     $v['vehicle_type'],
            status:          $v['status'], // <--- Sincronizado con el Request
            rejectionReason: $v['rejection_reason'] ?? null,
        );
    }
}