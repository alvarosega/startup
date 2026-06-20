<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transfer;

class TransferPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['logistics_manager', 'branch_admin', 'inventory_manager']);
    }

    public function view(User $user, Transfer $transfer): bool
    {
        // Solo pueden ver si la transferencia salió de su sucursal O va hacia su sucursal
        if ($user->hasRole(['branch_admin', 'logistics_manager'])) {
            return $transfer->origin_branch_id === $user->branch_id 
                || $transfer->destination_branch_id === $user->branch_id;
        }
        return true;
    }

    public function create(User $user): bool
    {
        // Todos estos roles pueden INICIAR una transferencia (Salida)
        return $user->hasAnyRole(['logistics_manager', 'branch_admin', 'inventory_manager']);
    }
    
    // Para recibir (Aprobar ingreso en destino)
    public function receive(User $user, Transfer $transfer): bool
    {
        // Solo si soy SuperAdmin O si soy el Admin de la sucursal de destino
        if ($user->hasRole('super_admin')) return true;
        
        return $transfer->destination_branch_id === $user->branch_id 
            && $transfer->status === 'in_transit';
    }
}