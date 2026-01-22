<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sku;

class SkuPolicy
{
    // 1. LA LLAVE MAESTRA
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    // 2. ESCRITURA: Solo Logística
    public function create(User $user): bool
    {
        return $user->hasRole('logistics_manager');
    }

    public function delete(User $user, Sku $sku): bool
    {
        return $user->hasRole('logistics_manager');
    }
}