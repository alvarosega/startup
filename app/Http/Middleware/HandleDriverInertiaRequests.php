<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleDriverInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        // Instanciación única del usuario autenticado en el silo correspondiente
        $user = $request->user('driver');

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $this->resolveDriverName($user),
                ] : null,
            ],
            'errors' => function () use ($request) {
                $errors = $request->session()->get('errors');
                return $errors ? $errors->getBag('default')->getMessages() : (object) [];
            },
        ]);
    }

    private function resolveDriverName($user): string
    {
        // Rendimiento Extremo: Solo retorna el nombre si el controlador hizo Eager Loading.
        // Queda estrictamente prohibido disparar queries en esta capa.
        if ($user->relationLoaded('details') && $user->details) {
            return trim($user->details->first_name . ' ' . $user->details->last_name);
        }

        return 'Conductor';
    }
}