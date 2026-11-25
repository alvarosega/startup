<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Staff;

use App\Models\Users\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListStaffAction
{
    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = Admin::query()->with(['branch', 'roles', 'permissions']);

        if (!empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($term): void {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if (isset($filters['branch_id']) && $filters['branch_id'] !== '') {
            if ($filters['branch_id'] === 'global') {
                $query->whereNull('branch_id');
            } else {
                $query->where('branch_id', $filters['branch_id']);
            }
        }

        if (!empty($filters['role'])) {
            $roleName = (string) $filters['role'];
            $query->whereHas('roles', function (Builder $q) use ($roleName): void {
                $q->where('name', $roleName)->where('guard_name', 'super_admin');
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $paginated = $query->latest('created_at')->paginate(15)->withQueryString();

        // Transformación canónica para el frontend
        $paginated->through(function (Admin $admin): array {
            return [
                'id'            => (string) $admin->id,
                'first_name'    => (string) $admin->first_name,
                'last_name'     => (string) $admin->last_name,
                'full_name'     => (string) "{$admin->first_name} {$admin->last_name}",
                'email'         => (string) $admin->email,
                'phone'         => $admin->phone,
                'branch_id'     => $admin->branch_id,
                'branch'        => $admin->branch ? [
                    'id'   => (string) $admin->branch->id,
                    'name' => (string) $admin->branch->name,
                    'city' => (string) $admin->branch->city,
                ] : null,
                'is_active'     => (bool) $admin->is_active,
                'roles'         => $admin->getRoleNames()->values()->all(),
                'permissions'   => $admin->getPermissionNames()->values()->all(),
                'last_login_at' => $admin->last_login_at?->diffForHumans(),
                'last_seen_at'  => $admin->last_seen_at?->diffForHumans(),
                'created_at'    => $admin->created_at?->format('Y-m-d H:i'),
            ];
        });

        return $paginated;
    }
}
