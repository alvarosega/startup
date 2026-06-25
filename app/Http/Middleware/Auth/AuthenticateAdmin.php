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
     * Maneja la restricción de acceso y purga intrusiones cruzadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('super_admin')->check()) {
            Auth::shouldUse('super_admin');
            return $next($request);
        }

        foreach (['driver', 'customer'] as $intrusiveGuard) {
            if (Auth::guard($intrusiveGuard)->check()) {
                Auth::guard($intrusiveGuard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->header('X-Inertia')) {
                    abort(403, 'Sesión cruzada detectada e invalidada.');
                }

                return redirect()->route('login')->with('error', 'Sesión previa invalidada automáticamente.');
            }
        }

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            abort(403, 'User is not logged in.');
        }

        return redirect()->route('login');
    }
}