<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users\Driver;

use App\Models\Users\Driver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetDriversListAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Driver::with(['profile', 'branch', 'billingInfos']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
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

        return $query->orderBy('created_at', 'desc')->paginate(25);
    }
}