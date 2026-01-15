<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    // Roles y Permisos para lógica visual (v-if="can...")
                    'roles' => $user->getRoleNames(), 
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    
                    // Datos de contexto
                    'profile' => $user->profile,
                    'branch' => $user->branch, // Para mostrar "Sucursal: Zona Sur" en el header
                    'completion_percentage' => $user->completion_percentage,
                    
                    // Estado de verificación
                    'verification_status' => $user->verifications()->latest()->first()?->status,
                ]) : null,
            ],
            // Flash messages para las notificaciones (Toast)
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}