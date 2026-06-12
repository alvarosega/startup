<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDriver
{
    /**
     * Maneja la restricción del silo de conductores/repartidores.
     * Aplica la Opción B de invalidación automática ante sesiones cruzadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('driver')->check()) {
            return $next($request);
        }

        // PROTOCOLO OPCIÓN B: Escaneo de intrusión de otros silos en el mismo dominio
        foreach (['super_admin', 'customer'] as $intrusiveGuard) {
            if (Auth::guard($intrusiveGuard)->check()) {
                
                Auth::guard($intrusiveGuard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/driver/login')->with('error', 'Sesión previa invalidada automáticamente. Inicie sesión como conductor.');
            }
        }

        return redirect('/driver/login');
    }
}