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
use Illuminate\Support\Str;
use Tighten\Ziggy\Ziggy;
use App\Http\Resources\Customer\Category\CategoryResource;
use App\Actions\Customer\Category\GetCategoryDetailsAction; 


class HandleCustomerInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = Auth::guard('customer')->user();
        
        // 1. PROTOCOLO DE IDENTIDAD GUEST: Garantizamos que el UUID nunca sea null
        $guestUuid = $request->header('X-Guest-UUID') 
                   ?? $request->session()->get('guest_client_uuid') 
                   ?? (string) Str::uuid();

        if ($request->session()->get('guest_client_uuid') !== $guestUuid) {
            $request->session()->put('guest_client_uuid', $guestUuid);
        }

        // 2. OBTENCIÓN DE CONTEXTO SUCURSAL
        $shopService = app(ShopContextService::class);
        $branchId = $shopService->getActiveBranchId();

        return array_merge(parent::share($request), [
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            
            // RECTIFICACIÓN: Paso de los 3 argumentos obligatorios
            'cart' => app(GetCustomerCartAction::class)->execute(
                $guestUuid, 
                $user?->id, 
                $branchId
            ),
            
            // 3. MENÚ REACTIVO AL CONTEXTO: Filtrado por branchId
            // MODIFICAR el cierre de 'categories_menu'
            'categories_menu' => function() use ($branchId) {
                $version = cache()->get('admin_categories_version', 1);
                
                $data = cache()->remember("global_menu_br_{$branchId}_v{$version}", 86400, function() use ($branchId) {
                    return app(GetCategoryDetailsAction::class)->getGlobalMenu($branchId);
                });

                // REPARACIÓN CRÍTICA: Inyectar la lógica de representación (Placeholder)
                return CategoryResource::collection($data)->resolve();
            },
            
            'auth' => [
                'customer' => $user ? (new CustomerResource($user))->resolve() : null,
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