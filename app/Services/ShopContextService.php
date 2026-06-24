<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Operations\Branch;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;

class ShopContextService
{
    /**
     * Resuelve el ID de la sucursal activa aplicando jerarquía de contexto.
     */
    public function getActiveBranchId(): string
    {
        // Prioridad 1: Selección explícita en sesión (voluntad de navegación en tiempo real)
        if ($sessionBranchId = Session::get('shop_branch_id')) {
            return (string) $sessionBranchId;
        }

        // Prioridad 2: Sucursal vinculada al perfil del cliente autenticado
        $user = Auth::guard('customer')->user();
        if ($user && $user->branch_id) {
            return (string) $user->branch_id;
        }

        // Prioridad 3: Fallback global del sistema
        return $this->getDefaultBranchId();
    }

    /**
     * Recupera la sucursal default estricta con caché de protección de 24 horas.
     */
    public function getDefaultBranchId(): string
    {
        return Cache::remember('shop_default_branch_id', 86400, function () {
            $branchId = Branch::where('is_active', true)
                ->where('is_default', true) // Filtro directo de columna inmutable
                ->value('id');

            if (!$branchId) {
                throw new Exception("Error Crítico: No existe una sucursal activa por defecto configurada (is_default = true).");
            }

            return (string) $branchId;
        });
    }

    /**
     * Modifica el contexto de compra guardando el estado en la sesión web.
     */
    public function setContext(string $branchId, ?string $addressId = null): void
    {
        Session::put('shop_branch_id', $branchId);
        if ($addressId) {
            Session::put('shop_address_id', $addressId);
        }
    }

    /**
     * Evalúa si un usuario logueado carece de cobertura logística en su perfil.
     */
    public function isUserOutOfCoverage(): bool
    {
        $user = Auth::guard('customer')->user();
        if (!$user) {
            return false; // Los invitados no aplican a bloqueo de cuenta
        }

        // Si está logueado, su branch_id es null y no ha forzado un cambio de sucursal por sesión
        return is_null($user->branch_id) && !Session::has('shop_branch_id');
    }
}