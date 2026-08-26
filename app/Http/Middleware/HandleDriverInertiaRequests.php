<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleDriverInertiaRequests extends Middleware
{
    /**
     * Plantilla base de renderizado para el portal de conductores.
     *
     * @var string
     */
    protected $rootView = 'driver';

    /**
     * Determina la versión actual de los assets de Inertia.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define las propiedades globales compartidas con todas las vistas de Driver.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\Users\Driver|null $driver */
        $driver = Auth::guard('driver')->user();

        if ($driver) {
            $lastSeen = session('driver_last_seen_at');
            if (!$lastSeen || now()->diffInMinutes($lastSeen) >= 5) {
                $driver->updateQuietly(['last_seen_at' => now()]);
                session(['driver_last_seen_at' => now()]);
            }

            $driver->loadMissing(['profile', 'branch']);
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $driver ? [
                    'id'            => (string) $driver->id,
                    'name'          => $driver->profile 
                        ? trim("{$driver->profile->first_name} {$driver->profile->last_name}") 
                        : 'Driver',
                    'first_name'    => $driver->profile?->first_name ?? '',
                    'last_name'     => $driver->profile?->last_name ?? '',
                    'email'         => $driver->email,
                    'phone'         => $driver->phone,
                    'status'        => is_object($driver->status) ? $driver->status->value : (string) $driver->status,
                    'is_online'     => (bool) $driver->is_online,
                    'is_available'  => (bool) $driver->is_available,
                    'avatar_source' => $driver->profile?->avatar_source ?? 'avatar_1.svg',
                    'avatar_type'   => $driver->profile?->avatar_type ?? 'icon',
                    'branch'        => $driver->branch ? [
                        'id'   => (string) $driver->branch->id,
                        'name' => (string) $driver->branch->name,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}