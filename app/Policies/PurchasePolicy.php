<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\Purchase;

class PurchasePolicy
{
    public function before(Admin $user, $ability)
    {
        if ($user->hasRole('super_admin', 'super_admin')) {
            return true;
        }
    }

    public function viewAny(Admin $user): bool
    {
        return $user->hasAnyRole(['logistics_manager', 'branch_admin', 'inventory_manager', 'finance_manager'], 'super_admin');
    }

    public function view(Admin $user, Purchase $purchase): bool
    {
        if ($user->hasRole('branch_admin', 'super_admin')) {
            return $purchase->branch_id === $user->branch_id;
        }
        return true;
    }

    public function create(Admin $user): bool
    {
        return $user->hasAnyRole(['logistics_manager', 'branch_admin', 'inventory_manager'], 'super_admin');
    }

    public function delete(Admin $user, Purchase $purchase): bool
    {
        return $user->hasAnyRole(['logistics_manager', 'branch_admin', 'inventory_manager'], 'super_admin');
    }
}