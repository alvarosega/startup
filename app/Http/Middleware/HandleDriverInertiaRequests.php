<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Driver\Auth\DriverResource;

class HandleDriverInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $driver = Auth::guard('driver')->user();

        if ($driver) {
            // LEY DE EFICIENCIA: Solo actualizamos 'last_seen_at' si han pasado > 5 min
            $lastSeen = session('driver_last_seen_at');
            if (!$lastSeen || now()->diffInMinutes($lastSeen) >= 5) {
                $driver->updateQuietly(['last_seen_at' => now()]);
                session(['driver_last_seen_at' => now()]);
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                // Uso del Resource para garantizar tipado estricto y blindaje de datos
                'user' => $driver ? (new DriverResource($driver))->resolve() : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}