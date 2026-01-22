<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Provider;

class ProviderPolicy
{
    // El Super Admin siempre pasa
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    // VER LISTA Y DETALLES: Permitido para Gerentes y Jefes
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'logistics_manager', 
            'finance_manager', 
            'branch_admin', 
            'inventory_manager'
        ]);
    }

    public function view(User $user, Provider $provider): bool
    {
        return $this->viewAny($user);
    }

    // CREAR/EDITAR/BORRAR: EXCLUSIVO de Logística
    // El Branch Admin retornará FALSE aquí
    public function create(User $user): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function update(User $user, Provider $provider): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $user->hasRole('logistics_manager');
    }
}