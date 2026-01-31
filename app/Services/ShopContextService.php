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
        // 1. PRIORIDAD ABSOLUTA: Usuario Logueado
        // Si hay usuario, su sucursal manda sobre cualquier dato de sesión anterior.
        $user = auth()->user();
        
        if ($user && $user->branch_id) {
            // Si la sesión no coincide con el usuario, la corregimos silenciosamente
            if (Session::get('shop_branch_id') !== $user->branch_id) {
                $this->setContext($user->branch_id);
            }
            return $user->branch_id;
        }

        // 2. PRIORIDAD INVITADO: Sesión
        // Si no hay usuario, respetamos si el invitado eligió una sucursal manualmente
        if (Session::has('shop_branch_id')) {
            return (int) Session::get('shop_branch_id');
        }

        // 3. FALLBACK: Sucursal Matriz
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