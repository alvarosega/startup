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
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? array_merge($request->user()->toArray(), [
                    'roles' => $request->user()->roles->pluck('name'),
                    'profile' => $request->user()->profile,
                    'completion_percentage' => $request->user()->completion_percentage,
                    // ENVIAR EL ESTADO DE LA ÚLTIMA VERIFICACIÓN
                    'verification_status' => $request->user()->verifications()->latest()->first()?->status,
                ]) : null,
            ],
        ]);
    }
}