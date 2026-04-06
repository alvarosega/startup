<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
// RECTIFICACIÓN: Usamos el Resource del perfil que ya contiene el 'status' y 'profile'
use App\Http\Resources\Driver\Profile\DriverProfileResource; 

class HandleDriverInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $driver = Auth::guard('driver')->user();

        if ($driver) {
            // LEY DE EFICIENCIA
            $lastSeen = session('driver_last_seen_at');
            if (!$lastSeen || now()->diffInMinutes($lastSeen) >= 5) {
                $driver->updateQuietly(['last_seen_at' => now()]);
                session(['driver_last_seen_at' => now()]);
            }
            
            // CRÍTICO: Cargar la relación antes de pasarla al Resource
            $driver->loadMissing(['profile', 'branch']);
        }

        return array_merge(parent::share($request), [
            'auth' => [
                // Blindaje de datos y tipado estricto
                'user' => $driver ? (new DriverProfileResource($driver))->resolve() : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}