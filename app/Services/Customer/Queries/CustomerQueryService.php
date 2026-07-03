<?php

declare(strict_types=1);

namespace App\Services\Customer\Queries;

use App\Models\Users\Customer;

final class CustomerQueryService
{
    public function phoneExists(string $phone, ?string $ignoreId = null): bool
    {
        $query = Customer::query()->where('phone', $phone);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function emailExists(string $email, ?string $ignoreId = null): bool
    {
        $query = Customer::query()->where('email', $email);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function findIdempotentCustomer(string $idempotencyKey): ?Customer
    {
        /** @var Customer|null */
        return Customer::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }
    public function getDefaultAddressAlias(string $customerId): ?string
    {
        return \App\Models\Users\CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->where('is_default', true)
            ->where('deleted_epoch', 0)
            ->value('alias');
    }

    public function getAddressesForCustomer(string $customerId): array
    {
        return \App\Models\Users\CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->where('deleted_epoch', 0)
            ->orderBy('is_default', 'desc')
            ->get()
            ->map(function (\App\Models\Users\CustomerAddress $addr) {
                /** @var array{lat: float, lng: float} $position */
                $position = $addr->position;

                return [
                    'id'         => $addr->id,
                    'alias'      => $addr->alias ?? '',
                    'address'    => $addr->address,
                    'reference'  => $addr->reference,
                    'is_default' => (bool) $addr->is_default,
                    'branch_id'  => $addr->branch_id,
                    'latitude'   => $position['lat'],
                    'longitude'  => $position['lng'],
                ];
            })->toArray();
    }

    public function getAddressByIdForCustomer(string $addressId, string $customerId): ?array
    {
        /** @var \App\Models\Users\CustomerAddress|null $addr */
        $addr = \App\Models\Users\CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customerId)
            ->where('deleted_epoch', 0)
            ->first();

        if (!$addr) {
            return null;
        }

        /** @var array{lat: float, lng: float} $position */
        $position = $addr->position;

        return [
            'id'         => $addr->id,
            'alias'      => $addr->alias ?? '',
            'address'    => $addr->address,
            'reference'  => $addr->reference,
            'branch_id'  => $addr->branch_id,
            'latitude'   => $position['lat'],
            'longitude'  => $position['lng'],
        ];
    }

    public function countActiveAddresses(string $customerId): int
    {
        return \App\Models\Users\CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->where('deleted_epoch', 0)
            ->count();
    }
}