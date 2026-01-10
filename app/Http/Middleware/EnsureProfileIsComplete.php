<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Si está logueado pero no tiene perfil o nombre (Nivel 1)
        if ($user && (!$user->profile || !$user->profile->first_name)) {
            // Permitir solo la ruta de completar perfil para evitar bucles
            if (!$request->is('complete-profile*') && !$request->is('logout')) {
                return redirect()->route('profile.complete');
            }
        }

        return $next($request);
    }
}