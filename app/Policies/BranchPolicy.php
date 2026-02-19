<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Branch;

class BranchPolicy
{
    // Solo super_admin con permiso específico puede gestionar sucursales
    public function viewAny(Admin $admin): bool {
        return $admin->can('manage_catalog');
    }

    public function create(Admin $admin): bool {
        return $admin->hasRole('super_admin');
    }

    public function update(Admin $admin, Branch $branch): bool {
        return $admin->hasRole('super_admin');
    }

    public function delete(Admin $admin, Branch $branch): bool {
        // No se puede eliminar la sucursal por defecto del sistema
        return $admin->hasRole('super_admin') && !$branch->is_default;
    }
}