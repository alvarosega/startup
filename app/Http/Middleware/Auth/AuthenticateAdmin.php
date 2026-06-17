<?php

declare(strict_types=1);

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    /**
     * Maneja la restricción del silo de administración.
     * Almacena el guard en el contenedor y purga intrusiones cruzadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado, forzamos el contexto del guard y continuamos
        if (Auth::guard('super_admin')->check()) {
            // LEY MULTI-GUARD: Obliga a Laravel a usar este guard para Gates y Políticas en esta petición
            Auth::shouldUse('super_admin');
            
            return $next($request);
        }

        // PROTOCOLO OPCIÓN B: Escaneo de intrusión de otros silos en el mismo dominio
        foreach (['driver', 'customer'] as $intrusiveGuard) {
            if (Auth::guard($intrusiveGuard)->check()) {
                
                Auth::guard($intrusiveGuard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->header('X-Inertia')) {
                    abort(403, 'Sesión cruzada detectada e invalidada.');
                }

                return redirect()->route('admin.login')->with('error', 'Sesión previa invalidada automáticamente. Inicie sesión con credenciales administrativas.');
            }
        }

        // Si no hay sesión alguna y es una petición asíncrona (Axios/Inertia), abortamos limpiamente
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            abort(403, 'User is not logged in.');
        }

        // Redirección segura utilizando el mapa de nombres dinámico
        return redirect()->route('admin.login');
    }
}