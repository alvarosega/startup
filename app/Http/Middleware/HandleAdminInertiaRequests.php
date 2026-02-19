<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleAdminInertiaRequests extends Middleware
{
    protected $rootView = 'app'; 

    public function share(Request $request): array
    {
        $admin = Auth::guard('super_admin')->user();
    
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $admin ? [
                    // CRÍTICO: Ya no usamos bin2hex. El ID ya es un String UUID.
                    'id'         => $admin->id, 
                    'first_name' => $admin->first_name,
                    'last_name'  => $admin->last_name,
                    'full_name'  => $admin->first_name . ' ' . $admin->last_name,
                    'email'      => $admin->email,
                    'roles'      => $admin->getRoleNames(), 
                    'can' => [
                        'manage_users'   => $admin->can('manage_users'),
                        'manage_drivers' => $admin->can('manage_drivers'),
                        'manage_catalog' => $admin->can('manage_catalog'),
                    ],
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            // Agregamos los errores de validación para que los formularios de admin funcionen
            'errors' => fn () => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object) [],
        ]);
    }
}