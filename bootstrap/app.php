<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'inertia.customer'   => \App\Http\Middleware\HandleCustomerInertiaRequests::class,
            'inertia.admin'      => \App\Http\Middleware\HandleAdminInertiaRequests::class,
            'inertia.driver'     => \App\Http\Middleware\HandleDriverInertiaRequests::class, 
        ]);

        // --- REDIRECCIÓN DE USUARIOS LOGUEADOS (Preventivo) ---
        $middleware->redirectUsersTo(function (Request $request) {
            $adminPath = env('ADMIN_PATH', 'adm'); // Unificamos fallback a 'adm'
        
            if (Auth::guard('super_admin')->check()) {
                return "/{$adminPath}/dashboard"; 
            }
            
            if (Auth::guard('driver')->check()) {
                return '/driver/dashboard';
            }
        
            return '/'; // Fallback para Customer
        });

        // --- REDIRECCIÓN DE INVITADOS (NO LOGUEADOS) ---
        $middleware->redirectGuestsTo(function (Request $request) {
            $adminPath = env('ADMIN_PATH', 'adm');

            // Detectar si el intento de acceso es al silo administrativo
            if ($request->is($adminPath . '/*') || $request->is($adminPath)) {
                return route('admin.login'); 
            }

            // Detectar si es al silo de conductores
            if ($request->is('driver/*') || $request->is('driver')) {
                return route('driver.login');
            }

            // Por defecto: login de cliente
            return route('login'); 
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();