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

        // Verificamos si falta el perfil o el nombre
        if ($user && (!$user->profile || !$user->profile->first_name)) {
            
            // CORRECCIÓN: Usar los nombres de ruta y URL reales definidos en web.php
            // Excluimos 'profile.wizard' y sus pasos, y el logout.
            if (!$request->routeIs('profile.wizard') && 
                !$request->routeIs('profile.step*') && 
                !$request->routeIs('logout')) {
                
                return redirect()->route('profile.wizard');
            }
        }

        return $next($request);
    }
}