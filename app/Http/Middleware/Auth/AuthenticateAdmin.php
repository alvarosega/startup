<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    /**
     * Maneja la restricción del silo de administración.
     * Aplica la Opción B de invalidación automática ante sesiones cruzadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario ya está autenticado como administrador, continúa sin obstrucciones
        if (Auth::guard('super_admin')->check()) {
            return $next($request);
        }

        // PROTOCOLO OPCIÓN B: Escaneo de intrusión de otros silos en el mismo dominio
        foreach (['driver', 'customer'] as $intrusiveGuard) {
            if (Auth::guard($intrusiveGuard)->check()) {
                
                // Cierre de sesión explícito del guard intruso
                Auth::guard($intrusiveGuard)->logout();

                // Destrucción total del payload de la sesión en el servidor
                $request->session()->invalidate();

                // Regeneración del token CSRF para mitigar ataques de fijación de sesión
                $request->session()->regenerateToken();

                return redirect('/admin/login')->with('error', 'Sesión previa invalidada automáticamente. Inicie sesión con credenciales administrativas.');
            }
        }

        // Si no hay sesión alguna, redirección limpia al login de admins
        return redirect('/admin/login');
    }
}