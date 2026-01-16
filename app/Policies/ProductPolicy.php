<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    // 1. LA LLAVE MAESTRA: Esto permite que el Super Admin haga TODO
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    // 2. LECTURA: Abierto a todos los roles operativos
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

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    // 3. ESCRITURA: Solo Logística (El Super Admin entra por el 'before')
    public function create(User $user): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole('logistics_manager');
    }
    // ESTO ES OBLIGATORIO PARA QUE EL SUPER ADMIN PASE

}