<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleAdminInertiaRequests extends Middleware
{
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

        $userData = $admin ? [
            'id'             => (string) $admin->id,
            'first_name'     => (string) $admin->first_name,
            'last_name'      => (string) $admin->last_name,
            'full_name'      => (string) "{$admin->first_name} {$admin->last_name}",
            'email'          => (string) $admin->email,
            'branch_id'      => $admin->branch_id ? (string) $admin->branch_id : null,
            'branch'         => $admin->branch ? [
                'id'   => (string) $admin->branch->id,
                'name' => (string) $admin->branch->name,
                'city' => (string) $admin->branch->city,
            ] : null,
            'is_super_admin' => $admin->hasRole('super_admin'),
            'roles'          => $admin->getRoleNames(),
            'permissions'    => [
                'manage_users'            => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_users')),
                'manage_drivers'          => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_drivers')),
                'manage_catalog'          => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_catalog')),
                'manage_branches'         => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_branches')),
                'manage_branch_schedules' => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_branch_schedules')),
                'manage_branch_cash'      => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_branch_cash')),
                'manage_inventory'        => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_inventory')),
                'manage_prices'           => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_prices')),
                'manage_purchases'        => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_purchases')),
                'manage_removals'         => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_removals')),
                'manage_orders'           => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_orders')),
                'manage_dispatch'         => (bool) ($admin->hasRole('super_admin') || $admin->can('manage_dispatch')),
                'view_logistics_radar'    => (bool) ($admin->hasRole('super_admin') || $admin->can('view_logistics_radar')),
            ],
        ] : null;

        return array_merge(parent::share($request), [
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'auth' => [
                'user' => $userData,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}