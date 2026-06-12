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
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // SILO: ADMIN
            \Illuminate\Support\Facades\Route::middleware(['web', 'inertia.admin'])
                ->prefix(env('ADMIN_PATH', 'adm'))
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // SILO: CUSTOMER
            \Illuminate\Support\Facades\Route::middleware(['web', 'inertia.customer'])
                ->name('customer.')
                ->group(base_path('routes/customer.php'));

            // SILO: DRIVER
            \Illuminate\Support\Facades\Route::middleware(['web', 'inertia.driver'])
                ->prefix('driver')
                ->name('driver.')
                ->group(base_path('routes/driver.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->alias([
            // Middlewares de Spatie
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            
            // Middlewares de Compartición de Estado de Inertia
            'inertia.customer'   => \App\Http\Middleware\HandleCustomerInertiaRequests::class,
            'inertia.admin'      => \App\Http\Middleware\HandleAdminInertiaRequests::class,
            'inertia.driver'     => \App\Http\Middleware\HandleDriverInertiaRequests::class, 
            
            // CORRECCIÓN: Registro de los 3 Middlewares de Control de Acceso (Opción B)
            'auth.admin'         => \App\Http\Middleware\Auth\AuthenticateAdmin::class,
            'auth.driver'        => \App\Http\Middleware\Auth\AuthenticateDriver::class,
            'auth.customer'      => \App\Http\Middleware\Auth\AuthenticateCustomer::class,

            // Otros utilitarios
            'idempotency'        => \App\Http\Middleware\CheckIdempotency::class,
        ]);

        // --- REDIRECCIÓN DE USUARIOS LOGUEADOS (Preventivo) ---
        $middleware->redirectUsersTo(function (Request $request) {
            $adminPath = env('ADMIN_PATH', 'adm');
        
            if (Auth::guard('super_admin')->check()) {
                return "/{$adminPath}/dashboard"; 
            }
            
            if (Auth::guard('driver')->check()) {
                return '/driver/dashboard';
            }
        
            return '/';
        });

        // --- REDIRECCIÓN DE INVITADOS (NO LOGUEADOS) ---
        $middleware->redirectGuestsTo(function (Request $request) {
            $adminPath = env('ADMIN_PATH', 'adm');

            if ($request->is($adminPath . '/*') || $request->is($adminPath)) {
                return route('admin.login'); 
            }

            if ($request->is('driver/*') || $request->is('driver')) {
                return route('driver.login');
            }

            return route('login'); 
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // REPORTAR ERRORES REALES AL LOG ANTES DE QUE INERTIA COLAPSE
        $exceptions->report(function (\Throwable $e) {
            \Illuminate\Support\Facades\Log::emergency('--- ERROR CRÍTICO DETECTADO ---');
            \Illuminate\Support\Facades\Log::emergency($e->getMessage());
            \Illuminate\Support\Facades\Log::emergency('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        });
    })->create();