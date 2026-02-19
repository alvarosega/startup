<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleDriverInertiaRequests extends Middleware
{
    protected $rootView = 'app';


    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => auth()->guard('driver')->user() ? [
                    'id' => auth()->guard('driver')->user()->id,
                    'email' => auth()->guard('driver')->user()->email,
                ] : null,
            ],
            // ESTA LÍNEA ES VITAL PARA QUE VUE MUESTRE LOS ERRORES
            'errors' => function () use ($request) {
                return $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
        ]);
    }

    private function resolveDriverName($user): string
    {
        // Cargamos la relación solo si no está cargada (Eager Loading preventivo)
        $details = $user->relationLoaded('details') ? $user->details : $user->details()->first();
        
        if ($details) {
            return trim($details->first_name . ' ' . $details->last_name);
        }

        return 'Conductor';
    }
}