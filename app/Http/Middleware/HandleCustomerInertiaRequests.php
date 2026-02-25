<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\CartItem;
use App\Models\Branch;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;

class HandleCustomerInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = Auth::guard('customer')->user();
        if ($user) { $user->load('profile'); }
        
        $guestUuid = $request->query('guest_id') ?? $request->header('X-Guest-UUID');
        $shopService = app(ShopContextService::class);
        $branchId = $shopService->getActiveBranchId();

        // 1. Resolvemos el contexto de la sucursal (Badge del Logo)
        $shopContext = $this->resolveShopContext($branchId);

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'        => $user->id,
                    'email'     => $user->email,
                    'name'      => $user->profile ? "{$user->profile->first_name} {$user->profile->last_name}" : 'Cliente',
                    'avatar'    => $user->profile?->avatar_source ?? 'avatar_1.svg',
                    'branch_id' => $user->branch_id,
                ] : null,
            ],
            
            // 2. Contexto de Ubicación (Cápsula Central)
            'location_context' => $this->resolveLocationContext($user, $shopContext['branch_name']),

            // 3. Contexto de Sucursal (Identidad Técnica)
            'shop_context' => $shopContext,

            // 4. Resumen de Carrito (Punto Rojo)
            'cart_summary' => [
                'count' => $this->getCartCount($user?->id, $guestUuid, $branchId),
            ],
        ]);
    }

    /**
     * Define qué texto mostrar en la cápsula central del header.
     */
    private function resolveLocationContext($user, string $branchName): array
    {
        if ($user) {
            // Buscamos el alias de la dirección predeterminada
            $defaultAddress = $user->addresses()->where('is_default', true)->first();
            
            return [
                'label' => $defaultAddress?->alias ?? 'Mi Ubicación',
                'type'  => 'address'
            ];
        }

        return [
            'label' => $branchName,
            'type'  => 'branch'
        ];
    }

    private function resolveShopContext(string $activeId): array
    {
        try {
            $branch = Branch::select('id', 'name', 'is_default')->find($activeId);
    
            return [
                'branch_id'   => $branch?->id ?? $activeId,
                'branch_name' => $branch?->name ?? 'Sucursal Central',
                'is_fallback' => $branch ? (bool)$branch->is_default : true,
            ];
        } catch (\Exception $e) {
            return [
                'branch_id'   => null,
                'branch_name' => 'Sin Cobertura',
                'is_fallback' => true,
            ];
        }
    }

    private function getCartCount($customerId, $guestUuid, $branchId): int
    {
        if (!$customerId && !$guestUuid) return 0;

        return CartItem::whereHas('cart', function($q) use ($customerId, $guestUuid, $branchId) {
            $q->where('branch_id', $branchId)
              ->where(function($sq) use ($customerId, $guestUuid) {
                  $customerId ? $sq->where('customer_id', $customerId) 
                              : $sq->where('session_id', $guestUuid);
              });
        })->sum('quantity') ?? 0;
    }
}