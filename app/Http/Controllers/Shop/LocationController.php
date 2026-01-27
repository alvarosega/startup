<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ShopContextService; // <--- 1. IMPORTANTE

class LocationController extends Controller
{
    /**
     * Cambia la dirección activa del usuario.
     * Sincroniza DB y Sesión para actualizar el inventario inmediatamente.
     */
    public function setLocation(Request $request, ShopContextService $contextService) // <--- 2. INYECCIÓN
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id' // Soporta UUIDs
        ]);

        $user = Auth::user();

        // Transacción para garantizar consistencia
        DB::transaction(function () use ($user, $request, $contextService) {
            
            // 1. Verificar propiedad (Seguridad)
            $address = UserAddress::where('user_id', $user->id)
                ->where('id', $request->address_id)
                ->firstOrFail();

            // 2. Lógica de "Radio Button": Solo una default
            // Optimizamos para no hacer update si ya es la default, aunque es opcional
            UserAddress::where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);

            // 3. Lógica de Negocio Crítica: Sincronizar Contexto
            if ($address->branch_id) {
                // A. Persistencia (Base de Datos)
                $user->update(['branch_id' => $address->branch_id]);

                // B. Navegación Actual (Sesión) - ¡ESTO FALTABA!
                // Esto le dice al sistema: "Olvida lo que tenías en memoria, 
                // ahora estamos en la sucursal de esta dirección".
                $contextService->setContext($address->branch_id, $address->id);
            }
        });

        return back()->with('success', 'Ubicación actualizada. Catálogo sincronizado.');
    }
}