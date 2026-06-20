<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Provider;

class ProviderPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('manage_catalog');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasRole('super_admin');
    }

    public function update(Admin $admin, Provider $provider): bool
    {
        return $admin->hasRole('super_admin');
    }

    public function delete(Admin $admin, Provider $provider): bool
    {
        return $admin->hasRole('super_admin');
    }
}