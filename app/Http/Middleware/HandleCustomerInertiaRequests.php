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
        
        // Obtención de IDs sin hits adicionales a DB si es posible
        $guestUuid = $request->header('X-Guest-UUID') ?? $request->session()->get('guest_client_uuid');
        $shopService = app(ShopContextService::class);
        $branchId = $shopService->getActiveBranchId();
    
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'        => (string) $user->id,
                    'name'      => $user->profile?->first_name ?? 'Cliente',
                    'avatar'    => $user->profile?->avatar_source ?? 'avatar_1.svg',
                    'branch_id' => (string) $user->branch_id,
                ] : null,
            ],
    
            // 1. CONTEXTOS: Cacheamos por petición (Request-Bound)
            'shop_context'     => $this->resolveShopContext($branchId),
            'location_context' => $this->resolveLocationContext($user, $branchId),
    
            // 2. DATOS PESADOS: Lazy Loading (Solo si el frontend los pide)
            'cart_summary' => \Inertia\Inertia::lazy(fn () => [
                'count' => $this->getCartCount($user?->id, $guestUuid, $branchId),
            ]),
    
            'active_order' => \Inertia\Inertia::lazy(function () use ($user) {
                if (!$user) return null;
                
                // Cacheamos el estado del pedido por 60 segundos para evitar hammering
                return cache()->remember("active_order_{$user->id}", 60, function() use ($user) {
                    $query = $user->orders()->whereIn('status', [
                        'pending_payment', 'under_review', 'preparing', 'dispatched', 'arrived'
                    ]);
                    
                    $latest = (clone $query)->latest()->first(['id', 'status', 'code']);
                    return [
                        'latest' => $latest,
                        'count'  => $query->count(),
                    ];
                });
            }),
        ]);
    }
    private function resolveLocationContext($user, string $branchId): array
    {
        if (!$user) {
            $branchName = cache()->remember("branch_name_{$branchId}", 86400, function() use ($branchId) {
                return \App\Models\Branch::where('id', $branchId)->value('name') ?? 'Sucursal';
            });
            return ['label' => $branchName, 'type' => 'branch'];
        }
    
        // Corrección: Usar la sesión correctamente (Session no tiene 'remember')
        $alias = session('user_addr_alias');
        if (!$alias) {
            $alias = $user->addresses()->where('is_default', true)->value('alias') ?? 'Mi Ubicación';
            session(['user_addr_alias' => $alias]);
        }
    
        return ['label' => $alias, 'type' => 'address'];
    }
    
    private function resolveShopContext(string $activeId): array
    {
        // Blindaje: Cachear el objeto de la sucursal para evitar el SELECT find() en cada request
        return cache()->remember("shop_context_{$activeId}", 3600, function() use ($activeId) {
            $branch = Branch::select('id', 'name', 'is_default')->find($activeId);
            return [
                'branch_id'   => $branch?->id ?? $activeId,
                'branch_name' => $branch?->name ?? 'Sucursal Central',
                'is_fallback' => $branch ? (bool)$branch->is_default : true,
            ];
        });
    }

    private function getCartCount($customerId, $guestUuid, $branchId): int
    {
        if (!$customerId && !$guestUuid) return 0;

        return (int) CartItem::whereHas('cart', function($q) use ($customerId, $guestUuid, $branchId) {
            $q->where('branch_id', $branchId);
            
            // CORRECCIÓN: Estructura IF explícita para garantizar la compilación SQL
            if ($customerId) {
                $q->where('customer_id', $customerId);
            } else {
                // Para invitados, blindamos asegurando que customer_id sea null
                $q->where('session_id', $guestUuid)
                  ->whereNull('customer_id');
            }
        })->sum('quantity');
    }
}