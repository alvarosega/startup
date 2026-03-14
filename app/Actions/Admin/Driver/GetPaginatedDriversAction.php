<?php

namespace App\Actions\Admin\Driver;

use App\Models\Driver;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPaginatedDriversAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        return Driver::with(['profile', 'branch']) // <-- CORRECCIÓN: 'profile' en lugar de 'details'
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('profile', function ($q2) use ($search) { // <-- 'profile'
                          $q2->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('license_plate', 'like', "%{$search}%");
                      });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                // El status vive en 'drivers', no hace falta whereHas
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }
}