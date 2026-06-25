<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;

class SearchDeletedCustomerAction
{
    /**
     * Busca una cuenta eliminada lógicamente y transforma su salida a un array nativo limpio.
     */
    public function execute(string $phone): ?array
    {
        $customer = Customer::onlyTrashed()
            ->with(['profile', 'branch'])
            ->where('phone', $phone)
            ->first();

        if (!$customer) {
            return null;
        }

        return [
            'id' => (string) $customer->id,
            'phone' => (string) $customer->phone,
            'email' => (string) $customer->email,
            'is_active' => (bool) $customer->is_active,
            'was_previously_deleted' => (bool) $customer->was_previously_deleted,
            'profile' => $customer->profile ? [
                'first_name' => (string) $customer->profile->first_name,
                'last_name' => (string) $customer->profile->last_name,
            ] : null,
            'branch' => $customer->branch ? [
                'id' => (string) $customer->branch->id,
                'name' => (string) $customer->branch->name,
            ] : null,
        ];
    }
}