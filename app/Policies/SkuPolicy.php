<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\Sku;

class SkuPolicy
{
    public function before(Admin $user, $ability)
    {
        if ($user->hasRole('super_admin', 'super_admin')) {
            return true;
        }
    }

    public function viewAny(Admin $user): bool
    {
        return true; // Permitido para cualquier administrador autenticado en el ERP
    }

    public function view(Admin $user, Sku $sku): bool
    {
        return true;
    }

    public function create(Admin $user): bool
    {
        return $user->hasRole('logistics_manager', 'super_admin');
    }

    public function delete(Admin $user, Sku $sku): bool
    {
        return $user->hasRole('logistics_manager', 'super_admin');
    }
}