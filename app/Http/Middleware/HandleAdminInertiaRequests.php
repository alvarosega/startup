<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Admin\Auth\AdminResource; 

class HandleAdminInertiaRequests extends Middleware
{
    /**
     * Se cambia la vista raíz para que use resources/views/admin.blade.php
     * @var string
     */
    protected $rootView = 'admin'; 

    public function share(Request $request): array
    {
        $admin = Auth::guard('super_admin')->user();
        
        if ($admin) {
            $lastSeen = session('admin_last_seen_at');
            if (!$lastSeen || now()->diffInMinutes($lastSeen) >= 5) {
                $admin->updateQuietly(['last_seen_at' => now()]);
                session(['admin_last_seen_at' => now()]);
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $admin ? (new AdminResource($admin))->resolve() : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'errors' => fn () => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object) [],
        ]);
    }
}