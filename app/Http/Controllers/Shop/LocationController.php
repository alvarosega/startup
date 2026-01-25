<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Cambia la dirección activa del usuario.
     * Esto afecta qué sucursal (y por ende qué stock) ve el usuario.
     */
    public function setLocation(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id'
        ]);

        $user = Auth::user();

        // Transacción para garantizar consistencia
        DB::transaction(function () use ($user, $request) {
            // 1. Verificar propiedad (Seguridad)
            $address = UserAddress::where('user_id', $user->id)
                ->where('id', $request->address_id)
                ->firstOrFail();

            // 2. Resetear todas a false
            UserAddress::where('user_id', $user->id)->update(['is_default' => false]);

            // 3. Activar la seleccionada
            $address->update(['is_default' => true]);

            // 4. Lógica de Negocio Crítica: Sincronizar Sucursal
            // Si cambio mi dirección de entrega a "Zona Sur", mi usuario debe
            // ver el stock de la sucursal "Zona Sur".
            if ($address->branch_id) {
                $user->update(['branch_id' => $address->branch_id]);
            }
        });

        return back()->with('success', 'Ubicación de entrega actualizada.');
    }
}