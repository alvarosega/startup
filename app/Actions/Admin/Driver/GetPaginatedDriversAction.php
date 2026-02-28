<?php

namespace App\Actions\Admin\Driver;

use App\Models\Driver;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPaginatedDriversAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        return Driver::with('details')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                      ->orWhereHas('details', function ($q2) use ($search) {
                          $q2->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('license_plate', 'like', "%{$search}%");
                      });
                });
            })
            ->when(($filters['status'] ?? null) === 'pending', function ($query) {
                $query->whereHas('details', fn($q) => $q->where('verification_status', 'pending'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }
}