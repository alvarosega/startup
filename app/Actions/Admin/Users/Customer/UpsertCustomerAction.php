<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Customer;
use App\DTOs\Admin\Users\Customer\UpsertCustomerDTO;
use App\Services\Geo\BranchCoverageService;
use Illuminate\Support\Facades\{DB, Hash, Cache};
use Illuminate\Support\Str;

final class UpsertCustomerAction
{
    public function __construct(protected BranchCoverageService $geoService) {}

    public function execute(UpsertCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($dto): Customer {
            // 1. REGLA 3.C: IDEMPOTENCY CHECK
            if ($dto->idempotencyKey) {
                $existing = Customer::where('idempotency_key', $dto->idempotencyKey)->first();
                if ($existing) return $existing;
            }

            // 2. REGLA 1.3: BLOQUEO PESIMISTA
            if ($dto->id) {
                Customer::where('id', $dto->id)->lockForUpdate()->firstOrFail();
            }

            // 3. ATOMIC DATA PREPARATION
            $finalBranchId = $this->geoService->identifyBranch($dto->latitude, $dto->longitude) ?? $dto->branchId;
            
            $customerData = [
                'email'           => $dto->email,
                'phone'           => $dto->phone,
                'branch_id'       => $finalBranchId,
                'is_active'       => $dto->isActive,
                'idempotency_key' => $dto->idempotencyKey,
                'latitude'        => $dto->latitude,
                'longitude'       => $dto->longitude,
            ];

            // Evitar sobrescritura de hash si no se provee password nuevo
            if ($dto->password) {
                $customerData['password'] = Hash::make($dto->password);
            }

            $customer = Customer::updateOrCreate(
                ['id' => $dto->id ?? (string) Str::uuid7()],
                $customerData
            );

            // 4. SYNC SIDE EFFECTS
            $this->syncProfile($customer, $dto);
            $this->syncAddress($customer, $dto, (string) $finalBranchId);

            // 5. CACHE PURGE (Regla 4.1)
            $this->purgeSiloCache();

            return $customer;
        });
    }

    private function syncProfile(Customer $customer, UpsertCustomerDTO $dto): void
    {
        $customer->profile()->updateOrCreate(
            ['customer_id' => $customer->id],
            [
                'first_name'    => mb_convert_encoding($dto->firstName, 'UTF-8'),
                'last_name'     => mb_convert_encoding($dto->lastName, 'UTF-8'),
                'avatar_source' => $dto->avatarSource ?? 'avatar_1.png',
            ]
        );
    }

    private function syncAddress(Customer $customer, UpsertCustomerDTO $dto, string $branchId): void
    {
        if (!$dto->address || !$dto->latitude) return;

        // El Admin gestiona la dirección principal (is_default = true)
        $customer->addresses()->updateOrCreate(
            [
                'customer_id' => $customer->id,
                'is_default'  => true
            ],
            [
                'id'         => (string) Str::uuid7(),
                'branch_id'  => $branchId,
                'alias'      => $dto->alias ?? 'PUNTO DE REGISTRO',
                'address'    => mb_convert_encoding($dto->address, 'UTF-8'),
                'reference'  => mb_convert_encoding($dto->details ?? '', 'UTF-8'),
                'latitude'   => $dto->latitude,
                'longitude'  => $dto->longitude,
            ]
        );
    }

    private function purgeSiloCache(): void
    {
        Cache::forget('admin_customers_list_base');
    }
}