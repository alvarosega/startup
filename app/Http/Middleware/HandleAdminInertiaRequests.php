<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleAdminInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'admin'; 

    public function share(Request $request): array
    {
        /** @var \App\Models\Users\Admin|null $admin */
        $admin = Auth::guard('super_admin')->user();
        
        if ($admin) {
            $lastSeen = session('admin_last_seen_at');
            if (!$lastSeen || now()->diffInMinutes($lastSeen) >= 5) {
                $admin->updateQuietly(['last_seen_at' => now()]);
                session(['admin_last_seen_at' => now()]);
            }
        }

        // Mapeo manual directo para preservar la inmutabilidad y pureza de Inertia sin dependencias REST API
        $userData = $admin ? [
            'id'          => (string) $admin->id,
            'first_name'  => (string) $admin->first_name,
            'last_name'   => (string) $admin->last_name,
            'full_name'   => (string) "{$admin->first_name} {$admin->last_name}",
            'email'       => (string) $admin->email,
            'roles'       => $admin->getRoleNames(),
            'permissions' => [
                'manage_users'   => (bool) $admin->can('manage_users'),
                'manage_drivers' => (bool) $admin->can('manage_drivers'),
                'manage_catalog' => (bool) $admin->can('manage_catalog'),
            ],
        ] : null;

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $userData,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'errors' => fn () => $request->session()->get('error')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object) [],
        ]);
    }
}