<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Cart;
use App\Models\Branch;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class HandleCustomerInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = Auth::guard('customer')->user();
        
        // Cargar perfil si existe
        if ($user) {
            $user->load('profile');
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    // BLINDAJE TOTAL: Convertir ID Binario a Hex
                    'id' => bin2hex($user->getRawOriginal('id') ?? $user->id),
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'name' => $user->profile 
                        ? ($user->profile->first_name . ' ' . $user->profile->last_name) 
                        : 'Cliente',
                    'avatar_source' => $user->profile?->avatar_source ?? 'avatar_1.svg',
                    'avatar_type' => $user->profile?->avatar_type ?? 'icon',
                    'branch_id' => $user->branch_id ? bin2hex($user->getRawOriginal('branch_id')) : null,
                ] : null,
                
                // MODIFICAR el map de addresses:
                'addresses' => $user ? $user->addresses()
                    ->select('id', 'alias', 'address', 'branch_id', 'is_default')
                    ->orderBy('is_default', 'desc')
                    ->get()
                    ->map(fn($addr) => [
                        'id'         => $this->toHex($addr->getRawOriginal('id')),
                        'alias'      => $addr->alias,
                        'address'    => $addr->address,
                        'branch_id'  => $this->toHex($addr->getRawOriginal('branch_id')),
                        'is_default' => (bool) $addr->is_default
                    ])
                    : [],
            ],

            'shop_context' => $this->resolveShopContext(),

            'active_branches' => fn () => Cache::remember('active_branches_list_v5', 3600, function () {
                try {
                // MODIFICAR el map dentro del Cache::remember:
                return Branch::where('is_active', true)
                    ->select('id', 'name')
                    ->get()
                    ->map(fn($b) => [
                        'id'   => $this->toHex($b->getRawOriginal('id')),
                        'name' => $b->name
                    ]);
                } catch (\Throwable $e) { return []; }
            }),

            'cart_count' => function () use ($request, $user) {
                try {
                    $query = Cart::query();
                    if ($user) $query->where('user_id', $user->id);
                    else $query->where('session_id', $request->session()->getId());
                    return (int) $query->withSum('items', 'quantity')->value('items_sum_quantity') ?? 0;
                } catch (\Throwable $e) { return 0; }
            },

            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }

    private function resolveShopContext()
    {
        try {
            $contextService = app(\App\Services\ShopContextService::class);
            $rawId = $contextService->getActiveBranchId();
            
            // LOG DE DEPURACIÓN
            \Illuminate\Support\Facades\Log::info('[Debug Context] Raw ID Type: ' . gettype($rawId));
            if (is_string($rawId)) {
                \Illuminate\Support\Facades\Log::info('[Debug Context] Raw ID Length: ' . strlen($rawId));
            }
    
            $activeBranchId = (is_string($rawId) && strlen($rawId) === 16) ? bin2hex($rawId) : $rawId;
            
            $activeBranchName = 'Tienda';
            if ($activeBranchId) {
                // Buscamos por el ID ya convertido a Hex
                $branch = \App\Models\Branch::find($activeBranchId); 
                if ($branch) $activeBranchName = $branch->name;
            }
    
            return [
                'branch_id' => $activeBranchId, 
                'branch_name' => $activeBranchName,
                'is_fallback' => (!$activeBranchId) && !session('shop_address_id'),
            ];
        } catch (\Throwable $e) {
            return ['branch_name' => 'Tienda', 'is_fallback' => true];
        }
    }
    private function toHex(mixed $value): mixed
    {
        if (is_string($value) && strlen($value) === 16 && !ctype_print($value)) {
            return bin2hex($value);
        }
        return $value;
    }
}