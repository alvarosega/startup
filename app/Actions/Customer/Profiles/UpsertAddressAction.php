<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\DTOs\Customer\Profiles\AddressData;
use App\Models\Users\CustomerAddress;
use App\Services\Customer\Queries\CustomerQueryService;
use App\Exceptions\Customer\Auth\DomainException; // Asegurar herencia de base DomainException
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UpsertAddressAction
{
    public function __construct(
        private readonly CustomerQueryService $customerQueryService
    ) {}

    public function execute(string $customerId, AddressData $dto, ?string $addressId = null): void
    {
        DB::transaction(function () use ($customerId, $dto, $addressId): void {
            $activeCount = $this->customerQueryService->countActiveAddresses($customerId);

            if ($addressId === null) {
                if ($activeCount >= 10) {
                    throw new DomainException('No es posible registrar la ubicación: Se ha excedido el límite estricto de 10 direcciones activas.');
                }
                
                // Si es la primera dirección del cliente, se fuerza como predeterminada
                $isDefault = $activeCount === 0;

                CustomerAddress::query()->create([
                    'id'            => (string) Str::uuid7(),
                    'customer_id'   => $customerId,
                    'branch_id'     => $dto->branchId,
                    'alias'         => mb_convert_encoding($dto->alias, 'UTF-8'),
                    'address'       => mb_convert_encoding($dto->address, 'UTF-8'),
                    'reference'     => $dto->reference !== null ? mb_convert_encoding($dto->reference, 'UTF-8') : null,
                    'position'      => ['lat' => $dto->latitude, 'lng' => $dto->longitude],
                    'is_default'    => $isDefault,
                    'deleted_epoch' => 0,
                ]);
                return;
            }

            /** @var CustomerAddress $address */
            $address = CustomerAddress::query()
                ->where('id', $addressId)
                ->where('customer_id', $customerId)
                ->where('deleted_epoch', 0)
                ->findOrFail($addressId);

            $address->update([
                'branch_id' => $dto->branchId,
                'alias'     => mb_convert_encoding($dto->alias, 'UTF-8'),
                'address'   => mb_convert_encoding($dto->address, 'UTF-8'),
                'reference' => $dto->reference !== null ? mb_convert_encoding($dto->reference, 'UTF-8') : null,
                'position'  => ['lat' => $dto->latitude, 'lng' => $dto->longitude],
            ]);
        });
    }
}