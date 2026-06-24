<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // SILO: ADMIN
            // Se remueve el ->name('admin.') global para evitar la sobrescritura y destrucción
            // de las rutas planas exigidas por el test inmutable de QA.
            Route::middleware(['web', 'inertia.admin'])
                ->prefix(env('ADMIN_PATH', 'adm'))
                ->group(base_path('routes/admin.php'));

            // SILO: CUSTOMER
            Route::middleware(['web', 'inertia.customer'])
                ->name('customer.')
                ->group(base_path('routes/customer.php'));

            // SILO: DRIVER
            Route::middleware(['web', 'inertia.driver'])
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
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            
            'inertia.customer'   => \App\Http\Middleware\HandleCustomerInertiaRequests::class,
            'inertia.admin'      => \App\Http\Middleware\HandleAdminInertiaRequests::class,
            'inertia.driver'     => \App\Http\Middleware\HandleDriverInertiaRequests::class, 
            
            'auth.admin'         => \App\Http\Middleware\Auth\AuthenticateAdmin::class,
            'auth.driver'        => \App\Http\Middleware\Auth\AuthenticateDriver::class,
            'auth.customer'      => \App\Http\Middleware\Auth\AuthenticateCustomer::class,

            'idempotency'        => \App\Http\Middleware\CheckIdempotency::class,
        ]);

        // --- REDIRECCIÓN DE USUARIOS LOGUEADOS ---
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
                return route('login'); 
            }

            if ($request->is('driver/*') || $request->is('driver')) {
                return route('driver.login');
            }

            return route('login'); 
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            \Illuminate\Support\Facades\Log::emergency('--- ERROR CRÍTICO DETECTADO ---');
            \Illuminate\Support\Facades\Log::emergency($e->getMessage());
            \Illuminate\Support\Facades\Log::emergency('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        });
    })->create();