<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCustomer
{
    /**
     * Maneja la restricción para rutas exclusivas de clientes autenticados.
     * Aplica la Opción B de invalidación automática ante sesiones cruzadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }

        // PROTOCOLO OPCIÓN B: Escaneo de intrusión de otros silos en el mismo dominio
        foreach (['super_admin', 'driver'] as $intrusiveGuard) {
            if (Auth::guard($intrusiveGuard)->check()) {
                
                Auth::guard($intrusiveGuard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'Sesión previa invalidada automáticamente. Inicie sesión como cliente.');
            }
        }

        return redirect('/login');
    }
}