<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Session;

class ShopContextService
{
    /**
     * Determina el ID de la sucursal activa para el contexto actual.
     * Regla: Sesión > Dirección Guardada > Sucursal Matriz (1)
     */
    public function getActiveBranchId(): int
    {
        // 1. Prioridad: Lo que está en la sesión
        if (Session::has('shop_branch_id')) {
            return (int) Session::get('shop_branch_id');
        }

        // 2. Si el usuario está logueado, buscar su última dirección usada o default
        $user = auth()->user();
        if ($user && $user->branch_id) {
            // Auto-reparamos la sesión para la próxima vez
            $this->setContext($user->branch_id);
            return $user->branch_id;
        }

        // 3. Fallback: Sucursal Matriz (ID 1)
        // Asegúrate de que tu sucursal principal tenga ID 1, si no cámbialo aquí.
        return 1; 
    }

    /**
     * Guarda el contexto en la sesión.
     * CORRECCIÓN: $addressId ahora acepta string (UUID) o int o null.
     */
    public function setContext(int $branchId, string|int|null $addressId = null): void
    {
        Session::put('shop_branch_id', $branchId);
        
        if ($addressId) {
            Session::put('shop_address_id', $addressId);
        }
    }
}