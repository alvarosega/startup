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
        
        // --- PROTOCOLO DE PERSISTENCIA DE IDENTIDAD ---
        // 1. Capturamos el UUID del header (enviado por el front)
        $headerUuid = $request->header('X-Guest-UUID');
        
        // 2. Si viene en el header y no está en la sesión, lo "fijamos" para futuros refrescos (F5)
        if ($headerUuid && $request->session()->get('guest_client_uuid') !== $headerUuid) {
            $request->session()->put('guest_client_uuid', $headerUuid);
        }

        // 3. Recuperamos el ID final (Prioridad: Sesión ya fijada)
        $guestUuid = $request->session()->get('guest_client_uuid');

        $shopService = app(\App\Services\ShopContextService::class);
        $branchId = $shopService->getActiveBranchId();

        return array_merge(parent::share($request), [
            // Ahora $guestUuid persistirá incluso si el header no se envía en un GET normal
            'cart' => app(\App\Actions\Customer\Cart\GetCustomerCartAction::class)->execute($guestUuid),

            'categories_menu' => app(\App\Actions\Customer\Shop\GetGlobalMenuAction::class)->execute(),

            'auth' => [
                'user' => $user ? [
                    'id'        => (string) $user->id,
                    'name'      => $user->profile?->first_name ?? 'Cliente',
                    'branch_id' => (string) $user->branch_id,
                ] : null,
            ],

            'shop_context'     => $this->resolveShopContext($branchId),
            'location_context' => $this->resolveLocationContext($user, $branchId),

            'active_order' => \Inertia\Inertia::lazy(fn () => 
                $user ? $this->resolveActiveOrder($user->id) : null
            ),
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