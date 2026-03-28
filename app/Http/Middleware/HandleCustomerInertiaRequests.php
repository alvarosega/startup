<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Inertia\Inertia;
use App\Models\Branch;
// REQUISITO: Este Resource debe existir en tu arquitectura
use App\Http\Resources\Customer\Auth\CustomerResource; 
use App\Actions\Customer\Cart\GetCustomerCartAction;
use App\Actions\Customer\Shop\GetGlobalMenuAction;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Customer\Category\CategoryResource;

use App\Actions\Customer\Category\GetCategoryDetailsAction; 


class HandleCustomerInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = Auth::guard('customer')->user();
        
        $headerUuid = $request->header('X-Guest-UUID');
        
        if ($headerUuid && $request->session()->get('guest_client_uuid') !== $headerUuid) {
            $request->session()->put('guest_client_uuid', $headerUuid);
        }

        $guestUuid = $request->session()->get('guest_client_uuid');

        $shopService = app(ShopContextService::class);
        $branchId = $shopService->getActiveBranchId();

        return array_merge(parent::share($request), [
            'cart'             => app(GetCustomerCartAction::class)->execute($guestUuid),
            'categories_menu' => app(GetCategoryDetailsAction::class)->getGlobalMenu(),
            'auth' => [
                'user' => $user ? (new CustomerResource($user))->resolve() : null,
            ],
            'shop_context'     => $this->resolveShopContext($branchId),
            'location_context' => $this->resolveLocationContext($user, $branchId),
            'active_order'     => Inertia::lazy(fn () => $user ? $this->resolveActiveOrder((string) $user->id) : null),
            
            // ESTANDARIZACIÓN DE CONTRATO FLASH
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
            ],
            'errors' => fn () => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object) [],
        ]);
    }

    private function resolveLocationContext($user, string $branchId): array
    {
        if (!$user) {
            $branchName = cache()->remember("branch_name_{$branchId}", 86400, function() use ($branchId) {
                return Branch::where('id', $branchId)->value('name') ?? 'Sucursal';
            });
            return ['label' => $branchName, 'type' => 'branch'];
        }
    
        $alias = session('user_addr_alias');
        if (!$alias) {
            $alias = $user->addresses()->where('is_default', true)->value('alias') ?? 'Mi Ubicación';
            session(['user_addr_alias' => $alias]);
        }
    
        return ['label' => $alias, 'type' => 'address'];
    }
    
    private function resolveShopContext(string $activeId): array
    {
        return cache()->remember("shop_context_{$activeId}", 3600, function() use ($activeId) {
            $branch = Branch::select('id', 'name', 'is_default')->find($activeId);
            return [
                'branch_id'   => $branch?->id ?? $activeId,
                'branch_name' => $branch?->name ?? 'Sucursal Central',
                'is_fallback' => $branch ? (bool)$branch->is_default : true,
            ];
        });
    }

    private function resolveActiveOrder(string $customerId): ?array
    {
        // PARCHE DE INTEGRIDAD: Previene el Error 500. 
        // Esta lógica debe ser extraída posteriormente a un Action (ej: GetCustomerActiveOrderAction).
        return null; 
    }
}