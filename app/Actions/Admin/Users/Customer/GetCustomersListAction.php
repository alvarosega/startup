<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;

class GetCustomersListAction
{
    /**
     * Filtra, pagina y transforma la lista de clientes para la presentación en Inertia.
     */
    public function execute(array $filters): array
    {
        $paginator = Customer::with(['profile', 'branch', 'addresses', 'billingInfos'])
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $mappedItems = array_map(function (Customer $customer) {
            $profile = $customer->profile;
            $branch = $customer->branch;

            return [
                'id' => (string) $customer->id,
                'phone' => (string) $customer->phone,
                'country_code' => $customer->country_code ? (string) $customer->country_code : null,
                'email' => (string) $customer->email,
                'trust_score' => (int) ($customer->trust_score ?? 0),
                'is_active' => (bool) $customer->is_active,
                'was_previously_deleted' => (bool) ($customer->was_previously_deleted ?? false),
                'needs_password_change' => (bool) ($customer->needs_password_change ?? false),
                'email_verified_at' => $customer->email_verified_at ? $customer->email_verified_at->toIso8601String() : null,
                'last_seen_at' => $customer->last_seen_at ? $customer->last_seen_at->toIso8601String() : null,
                'last_login_at' => $customer->last_login_at ? $customer->last_login_at->toIso8601String() : null,
                'created_at' => $customer->created_at ? $customer->created_at->toIso8601String() : null,
                'branch' => $branch ? [
                    'id' => (string) $branch->id,
                    'name' => (string) $branch->name,
                ] : null,
                'profile' => $profile ? [
                    'first_name' => (string) $profile->first_name,
                    'last_name' => (string) $profile->last_name,
                    'birth_date' => $profile->birth_date ? $profile->birth_date->format('Y-m-d') : null,
                    'gender' => $profile->gender ? (string) $profile->gender : null,
                    // CORRECCIÓN: Acceso seguro a la propiedad ->value del Backed Enum para evitar el colapso por conversión
                    'avatar_type' => $profile->avatar_type instanceof \BackedEnum ? $profile->avatar_type->value : ($profile->avatar_type ? (string) $profile->avatar_type : null),
                    'avatar_source' => $profile->avatar_source ? (string) $profile->avatar_source : null,
                ] : null,
            ];
        }, $paginator->items());

        return [
            'users' => $mappedItems,
            'pagination' => [
                'current_page' => (int) $paginator->currentPage(),
                'last_page' => (int) $paginator->lastPage(),
                'total' => (int) $paginator->total(),
            ]
        ];
    }
}