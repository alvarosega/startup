<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Session;

class ShopContextService
{
    // CAMBIAR EL MÉTODO getActiveBranchId:
    public function getActiveBranchId(): mixed // Quitar :int, permitir binario o null
    {
        $user = auth()->user();
        
        if ($user && $user->branch_id) {
            // Retornamos el binario puro del modelo
            return $user->getRawOriginal('branch_id'); 
        }

        if (Session::has('shop_branch_id')) {
            $sessionBranchId = Session::get('shop_branch_id');
            // Si viene de sesión como Hex, convertir a binario para el servicio
            return (is_string($sessionBranchId) && strlen($sessionBranchId) === 32) 
                ? hex2bin($sessionBranchId) 
                : $sessionBranchId;
        }

        return null; // El fallback debe ser null, no 1
    }

    // CAMBIAR EL MÉTODO setContext:
    public function setContext(mixed $branchId, mixed $addressId = null): void
    {
        // Guardamos en sesión como HEX para que sea seguro (UTF-8)
        $hexBranchId = (is_string($branchId) && strlen($branchId) === 16) ? bin2hex($branchId) : $branchId;
        Session::put('shop_branch_id', $hexBranchId);
        
        if ($addressId) {
            $hexAddressId = (is_string($addressId) && strlen($addressId) === 16) ? bin2hex($addressId) : $addressId;
            Session::put('shop_address_id', $hexAddressId);
        }
    }
}