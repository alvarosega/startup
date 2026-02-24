<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ShopContextService
{
    /**
     * Resuelve el ID de la sucursal activa bajo la jerarquía:
     * 1. Customer->branch_id (Si está logueado y dentro de cobertura)
     * 2. Session 'shop_branch_id' (Para invitados con ubicación seleccionada)
     * 3. Sucursal is_default (Fallback global)
     */
    public function getActiveBranchId(): string
    {
        // 1. Intento por Usuario Autenticado
        $user = Auth::guard('customer')->user();
        if ($user && $user->branch_id) {
            return $user->branch_id;
        }

        // 2. Intento por Sesión (Invitados o Clientes sin branch_id aún)
        $sessionBranchId = Session::get('shop_branch_id');
        if ($sessionBranchId) {
            return $sessionBranchId;
        }

        // 3. Fallback a Sucursal por Defecto
        return $this->getDefaultBranchId();
    }

    /**
     * Obtiene el ID de la sucursal por defecto con caché de 24h.
     * Si no hay ninguna marcada como default, toma la primera activa.
     */
    public function getDefaultBranchId(): string
    {
        return Cache::remember('shop_default_branch_id', 86400, function () {
            $branch = Branch::where('is_default', true)
                ->where('is_active', true)
                ->first() 
                ?? Branch::where('is_active', true)->first();

            if (!$branch) {
                throw new \Exception("Error Crítico: No existen sucursales activas en el sistema.");
            }

            return $branch->id;
        });
    }

    public function setContext(string $branchId, ?string $addressId = null): void
    {
        Session::put('shop_branch_id', $branchId);
        if ($addressId) {
            Session::put('shop_address_id', $addressId);
        }
    }
}