<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Cart;
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
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    // Roles y Permisos para lógica visual (v-if="can...")
                    'roles' => $user->getRoleNames(), 
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    
                    // Datos de contexto
                    'profile' => $user->profile,
                    'branch' => $user->branch, // Para mostrar "Sucursal: Zona Sur" en el header
                    'completion_percentage' => $user->completion_percentage,
                    
                    // Estado de verificación
                    'verification_status' => $user->verifications()->latest()->first()?->status,
                ]) : null,
                'addresses' => $request->user() 
                    ? $request->user()->addresses()
                        ->select('id', 'alias', 'address', 'branch_id', 'is_default')
                        ->orderBy('is_default', 'desc') // La default primero
                        ->get() 
                : [],
            ],
            'cart_count' => function () use ($request) {
                // 1. Identificar al dueño del carrito
                $query = Cart::query();
                
                if ($request->user()) {
                    $query->where('user_id', $request->user()->id);
                } else {
                    $query->where('session_id', $request->session()->getId());
                }

                // 2. Obtener la suma de cantidades (Items * Cantidad)
                // Usamos 'withSum' o accedemos a la relación si el carrito existe
                $cart = $query->withSum('items', 'quantity')->first();

                return $cart ? (int)$cart->items_sum_quantity : 0;
            },        
            // Flash messages para las notificaciones (Toast)
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}