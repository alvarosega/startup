<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckDriverStatus
{
    public function handle(Request $request, Closure $next)
    {
        $driver = $request->user('driver');

        // Intercepción: Si está pendiente, no pasa al controlador
        if ($driver && $driver->status === 'pending') {
            if (!$request->routeIs('driver.profile.*') && !$request->routeIs('driver.logout')) {
                return Inertia::render('Driver/Auth/WaitingApproval');
            }
        }

        return $next($request);
    }
}