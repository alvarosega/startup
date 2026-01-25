<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Branch; // <--- AGREGADO
use Inertia\Middleware;
use Illuminate\Support\Facades\Cache; // <--- AGREGADO

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'profile' => $user->profile,
                    'branch' => $user->branch,
                    'completion_percentage' => $user->completion_percentage, // Asegúrate de tener este accessor en User
                    'verification_status' => $user->verifications()->latest()->first()?->status,
                ]) : null,
                'addresses' => $user
                    ? $user->addresses()
                        ->select('id', 'alias', 'address', 'branch_id', 'is_default')
                        ->orderBy('is_default', 'desc')
                        ->get()
                    : [],
            ],
            
            'cart_count' => function () use ($request) {
                $query = Cart::query();
                if ($request->user()) {
                    $query->where('user_id', $request->user()->id);
                } else {
                    $query->where('session_id', $request->session()->getId());
                }
                // Optimizamos la suma para no hidratar todos los modelos
                return (int) $query->withSum('items', 'quantity')->value('items_sum_quantity') ?? 0;
            },

            // --- ESTO ES LO QUE FALTABA PARA EL REGISTRO ---
            'active_branches' => fn () => Cache::remember('active_branches_list', 3600, function () {
                return Branch::where('is_active', true)
                    ->select('id', 'name', 'coverage_polygon')
                    ->get();
            }),

            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}