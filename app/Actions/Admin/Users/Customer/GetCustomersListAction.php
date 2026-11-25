<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Customer;

use App\Models\Users\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCustomersListAction
{
    /**
     * Filtra, pagina y transforma la lista de clientes para la presentación en Inertia.
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $paginator = Customer::with(['profile', 'branch', 'addresses'])
            ->where('deleted_epoch', 0)
            ->filter($filters)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $paginator->through(function (Customer $customer): array {
            $profile = $customer->profile;
            $branch = $customer->branch;

            $firstName = $profile?->first_name ?? '';
            $lastName = $profile?->last_name ?? '';
            $fullName = trim("{$firstName} {$lastName}") ?: 'Sin nombre';

            return [
                'id'                     => (string) $customer->id,
                'first_name'             => $firstName,
                'last_name'              => $lastName,
                'full_name'              => $fullName,
                'phone'                  => (string) $customer->phone,
                'country_code'           => $customer->country_code ? (string) $customer->country_code : null,
                'email'                  => (string) $customer->email,
                'trust_score'            => (int) ($customer->trust_score ?? 0),
                'is_active'              => (bool) $customer->is_active,
                'was_previously_deleted' => (bool) ($customer->was_previously_deleted ?? false),
                'needs_password_change'  => (bool) ($customer->needs_password_change ?? false),
                'email_verified_at'      => $customer->email_verified_at?->diffForHumans(),
                'last_seen_at'           => $customer->last_seen_at?->diffForHumans(),
                'last_login_at'          => $customer->last_login_at?->diffForHumans(),
                'created_at'             => $customer->created_at?->format('Y-m-d H:i'),
                'addresses_count'        => $customer->addresses->count(),
                'branch'                 => $branch ? [
                    'id'   => (string) $branch->id,
                    'name' => (string) $branch->name,
                    'city' => (string) $branch->city,
                ] : null,
                'profile'                => $profile ? [
                    'first_name'    => (string) $profile->first_name,
                    'last_name'     => (string) $profile->last_name,
                    'birth_date'    => $profile->birth_date?->format('Y-m-d'),
                    'gender'        => $profile->gender ? (string) $profile->gender : null,
                    'avatar_type'   => $profile->avatar_type instanceof \BackedEnum ? $profile->avatar_type->value : ($profile->avatar_type ? (string) $profile->avatar_type : null),
                    'avatar_source' => $profile->avatar_source ? (string) $profile->avatar_source : null,
                ] : null,
            ];
        });

        return $paginator;
    }
}
