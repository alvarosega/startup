<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleDriverInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user('driver');

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $this->resolveDriverName($user),
                    'status'=> $user->status, // <--- CRÍTICO para la reactividad en el Frontend
                ] : null,
            ],
            // Compartimos los mensajes de éxito/error de la sesión
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }

    private function resolveDriverName($user): string
    {
        // CORRECCIÓN: Apuntar a la relación 'profile' (antes details)
        if ($user->relationLoaded('profile') && $user->profile) {
            return trim($user->profile->first_name . ' ' . $user->profile->last_name);
        }

        return 'Conductor';
    }
}