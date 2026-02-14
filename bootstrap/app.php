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
        
        $middleware->trustProxies(at: '*');

        // 1. Configuración Web
        $middleware->web(append: [
            // ELIMINADO: \App\Http\Middleware\HandleInertiaRequests::class,  <-- YA NO VA AQUÍ
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->encryptCookies(except: []);

        // 2. ALIAS (AQUÍ AGREGAMOS LOS NUEVOS)
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            
            // NUEVOS ALIAS:
            'inertia.customer' => \App\Http\Middleware\HandleCustomerInertiaRequests::class,
            'inertia.admin'    => \App\Http\Middleware\HandleAdminInertiaRequests::class,
            'inertia.driver'   => \App\Http\Middleware\HandleDriverInertiaRequests::class, 
        ]);

        // 3. REDIRECCIÓN (Sin cambios)
        $middleware->redirectGuestsTo(function (Request $request) {
            
            // Leemos la ruta del admin desde el .env (por seguridad)
            $adminPath = env('ADMIN_PATH', 'admin');

            // Si intenta entrar a cualquier ruta que empiece con /admin...
            if ($request->is($adminPath . '/*') || $request->is($adminPath)) {
                return route('admin.login'); 
            }
            if ($request->is('driver/*') || $request->is('driver')) {
                return route('driver.login');
            }
            // Para cualquier otro caso, lo mandamos al login de cliente
            return route('login'); 
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->booting(function () {
        RateLimiter::for('login', function (Request $request) {
            $identity = $request->input('email') ?: $request->input('phone');
            return Limit::perMinute(5)->by($identity . $request->ip());
        });
    })->create();