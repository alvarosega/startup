<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    // LECTURA: Abierta a casi todos los operativos
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'logistics_manager', 
            'finance_manager', 
            'branch_admin', 
            'inventory_manager',
            'growth_specialist'
        ]);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->viewAny($user);
    }

    // ESCRITURA: Exclusiva de Logística
    public function create(User $user): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasRole('logistics_manager');
    }
}