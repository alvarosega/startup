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
}