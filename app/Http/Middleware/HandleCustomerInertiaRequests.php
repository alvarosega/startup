<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Branch;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class HandleCustomerInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    // app/Http/Middleware/HandleCustomerInertiaRequests.php

    public function share(Request $request): array
    {
        $user = Auth::guard('customer')->user();
        if ($user) { $user->load('profile'); }
        $guestUuid = $request->query('guest_id') ?? $request->header('X-Guest-UUID');
        $branchId = app(ShopContextService::class)->getActiveBranchId();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'            => $user->id, // Acceso directo (UUID String)
                    'email'         => $user->email,
                    'name'          => $user->profile ? ($user->profile->first_name . ' ' . $user->profile->last_name) : 'Cliente',
                    'branch_id'     => $user->branch_id,
                ] : null,
                'addresses' => $user ? $user->addresses()
                    ->select('id', 'alias', 'address', 'branch_id', 'is_default')
                    ->get()
                    ->map(fn($addr) => [
                        'id'         => $addr->id,
                        'alias'      => $addr->alias,
                        'branch_id'  => $addr->branch_id,
                        'is_default' => (bool) $addr->is_default
                    ]) : [],
            ],
            'shop_context' => $this->resolveShopContext(),
            'cart_summary' => [
                'count' => $this->getCartCount($user?->id, $guestUuid, $branchId),
            ],
            
        ]);
    }

    private function resolveShopContext()
    {
        $service = app(\App\Services\ShopContextService::class);
        
        try {
            $activeId = $service->getActiveBranchId();
            
            // Buscamos solo los campos necesarios (Performance)
            $branch = \App\Models\Branch::select('id', 'name', 'is_default')->find($activeId);
    
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
    private function toHex(mixed $value): mixed
    {
        if (is_string($value) && strlen($value) === 16 && !ctype_print($value)) {
            return bin2hex($value);
        }
        return $value;
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