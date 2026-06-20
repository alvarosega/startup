<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Driver\Profile\DriverProfileResource; 

class HandleDriverInertiaRequests extends Middleware
{
    /**
     * Se direcciona estrictamente a resources/views/driver.blade.php o customer.blade.php
     * @var string
     */
    protected $rootView = 'driver';

    public function share(Request $request): array
    {
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
                'user' => $driver ? (new DriverProfileResource($driver))->resolve() : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}