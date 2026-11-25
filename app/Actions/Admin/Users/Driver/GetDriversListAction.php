<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Driver;

use App\Models\Users\Driver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetDriversListAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Driver::with(['profile', 'branch'])
            ->where('deleted_epoch', 0);

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('profile', function ($qp) use ($search) {
                      $qp->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        $paginator = $query->latest('created_at')->paginate(15)->withQueryString();

        $paginator->through(function (Driver $driver): array {
            $profile = $driver->profile;
            $branch = $driver->branch;

            $firstName = $profile?->first_name ?? '';
            $lastName = $profile?->last_name ?? '';
            $fullName = trim("{$firstName} {$lastName}") ?: 'Sin nombre';

            return [
                'id'                     => (string) $driver->id,
                'email'                  => (string) $driver->email,
                'phone'                  => (string) $driver->phone,
                'status'                 => is_object($driver->status) ? $driver->status->value : (string) $driver->status,
                'is_online'              => (bool) $driver->is_online,
                'is_available'           => (bool) $driver->is_available,
                'branch_id'              => $driver->branch_id,
                'branch'                 => $branch ? [
                    'id'   => (string) $branch->id,
                    'name' => (string) $branch->name,
                    'city' => (string) $branch->city,
                ] : null,
                'profile'                => $profile ? [
                    'first_name'         => (string) $profile->first_name,
                    'last_name'          => (string) $profile->last_name,
                    'full_name'          => $fullName,
                    'license_number'     => $profile->license_number,
                    'license_plate'      => $profile->license_plate,
                    'vehicle_type'       => $profile->vehicle_type,
                    'rejection_reason'   => $profile->rejection_reason,
                    'ci_front_path'      => $profile->ci_front_path,
                    'license_photo_path' => $profile->license_photo_path,
                    'vehicle_photo_path' => $profile->vehicle_photo_path,
                ] : null,
                'first_name'             => $firstName,
                'last_name'              => $lastName,
                'full_name'              => $fullName,
                'license_plate'          => $profile?->license_plate,
                'vehicle_type'           => $profile?->vehicle_type,
                'last_seen_at'           => $driver->last_seen_at?->diffForHumans(),
                'last_login_at'          => $driver->last_login_at?->diffForHumans(),
                'created_at'             => $driver->created_at?->format('Y-m-d H:i'),
            ];
        });

        return $paginator;
    }
}
