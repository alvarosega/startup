<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleDriverInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = auth()->guard('driver')->user();
    
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    // Usamos el ID ya procesado por el modelo
                    'id' => $user->id, 
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'name' => $user->details ? $user->details->first_name . ' ' . $user->details->last_name : 'Conductor',
                ] : null,
            ],
            // Datos específicos que solo necesita la App de Drivers
            'driver_config' => [
                'is_verified' => $user?->status === 'verified',
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}