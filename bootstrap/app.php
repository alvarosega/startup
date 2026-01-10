<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Configuración Web (Inertia + Sesiones)
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // 2. ALIAS PARA PERMISOS (SOLUCIÓN AL ERROR 500)
        // Esto permite usar 'role:super_admin' en las rutas
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->booting(function () {
        // 3. Rate Limiter para Login (5 intentos por minuto)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->phone . $request->ip());
        });
    })->create();