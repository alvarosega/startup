<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleAdminInertiaRequests extends Middleware
{
    // Usamos 'app' porque no tienes 'admin.blade.php'
    protected $rootView = 'app'; 

    public function share(Request $request): array
    {
        $admin = Auth::guard('admin')->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $admin ? [
                    'id' => bin2hex($admin->getRawOriginal('id') ?? $admin->id),
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'email' => $admin->email,
                    // IMPORTANTE: Enviamos 'roles' como array para que el Sidebar no falle
                    'roles' => [$admin->role_level], 
                ] : null, // Si es null, el JS debe manejarlo
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}