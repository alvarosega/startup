<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    // Antes de nada: El Super Admin siempre pasa.
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    // ¿Quién puede ver la lista? (Index)
    public function viewAny(User $user): bool
    {
        // SOLO Branch Admin (y Super Admin por el before). Nadie más.
        return $user->hasRole('branch_admin');
    }

    // ¿Quién puede ver un usuario específico? (Show/Edit)
    public function view(User $user, User $model): bool
    {
        // Branch Admin solo si es de su sucursal
        return $user->hasRole('branch_admin') && $user->branch_id === $model->branch_id;
    }

    // ¿Quién puede crear? (Create/Store)
    public function create(User $user): bool
    {
        return $user->hasRole('branch_admin');
    }

    // ¿Quién puede editar? (Edit/Update)
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('branch_admin') && $user->branch_id === $model->branch_id;
    }

    // ¿Quién puede eliminar? (Destroy)
    public function delete(User $user, User $model): bool
    {
        // Opcional: Impedir que se borren a sí mismos
        if ($user->id === $model->id) return false;

        return $user->hasRole('branch_admin') && $user->branch_id === $model->branch_id;
    }
}