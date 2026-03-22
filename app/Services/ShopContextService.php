<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ShopContextService
{
    public function getActiveBranchId(): string
    {
        // Prioridad 1: Usuario (Si el usuario es Admin o Driver, este silo debe devolver null/fallback)
        $user = Auth::guard('customer')->user();
        if ($user && $user->branch_id) {
            return (string) $user->branch_id;
        }
    
        // Prioridad 2: Selección explícita en sesión
        if ($sessionBranchId = session('shop_branch_id')) {
            return (string) $sessionBranchId;
        }
    
        // Prioridad 3: Fallback (Ya optimizado con Cache 24h)
        return $this->getDefaultBranchId();
    }

    
    public function getDefaultBranchId(): string
    {
        return Cache::remember('shop_default_branch_id', 86400, function () {
            // Solo traemos el ID, cumpliendo con la Query Law
            $branchId = Branch::where('is_active', true)
                ->orderBy('is_default', 'desc') // Prioriza la default si existe
                ->value('id');
    
            if (!$branchId) {
                throw new \Exception("Error Crítico: No existen sucursales activas.");
            }
    
            return (string) $branchId;
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