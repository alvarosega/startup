<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Users\Customer;
use App\Services\Customer\Queries\BranchQueryService;
use App\Services\Customer\Queries\CustomerQueryService;
use App\Services\ShopContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

final class HandleCustomerInertiaRequests extends Middleware
{
    /**
     * Se direcciona estrictamente a resources/views/customer.blade.php
     * @var string
     */
    protected $rootView = 'customer';

    public function __construct(
        private readonly ShopContextService $shopContextService,
        private readonly BranchQueryService $branchQueryService,
        private readonly CustomerQueryService $customerQueryService
    ) {}

    /**
     * Determina las propiedades compartidas globalmente con la interfaz de Inertia.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var Customer|null $user */
        $user = Auth::guard('customer')->user();
        
        $branchId = $this->shopContextService->getActiveBranchId();

        return array_merge(parent::share($request), [
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            
            'cart' => null,
            'categories_menu' => [],
            'active_order' => null,
            
            'auth' => [
                'customer' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile' => $user->profile ? [
                        'first_name' => $user->profile->first_name,
                        'last_name' => $user->profile->last_name,
                        'avatar_type' => $user->profile->avatar_type,
                        'avatar_source' => $user->profile->avatar_source,
                    ] : null,
                ] : null,
            ],
            
            'shop_context' => $this->resolveShopContext($branchId),
            'location_context' => $this->resolveLocationContext($user, $branchId),
        
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

    /**
     * @return array{label: string, type: string}
     */
    private function resolveLocationContext(?Customer $user, string $branchId): array
    {
        if (!$user) {
            $branchName = $this->branchQueryService->getBranchNameById($branchId);
            return [
                'label' => $branchName ?? 'Sucursal', 
                'type' => 'branch'
            ];
        }
    
        $alias = $this->customerQueryService->getDefaultAddressAlias($user->id);
    
        return [
            'label' => $alias ?? 'Mi Ubicación', 
            'type' => 'address'
        ];
    }
    
    /**
     * @return array{branch_id: string, branch_name: string, is_fallback: bool, is_out_of_coverage: bool}
     */
    private function resolveShopContext(string $activeId): array
    {
        $isOutOfCoverage = $this->shopContextService->isUserOutOfCoverage();
        $branchData = $this->branchQueryService->getBranchContextData($activeId);

        return [
            'branch_id'          => $branchData['id'] ?? $activeId,
            'branch_name'        => $branchData['name'] ?? 'Sucursal Central',
            'is_fallback'        => $branchData['is_default'] ?? true,
            'is_out_of_coverage' => $isOutOfCoverage,
        ];
    }
}