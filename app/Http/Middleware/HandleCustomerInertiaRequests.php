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

    // app/Http/Middleware/HandleCustomerInertiaRequests.php

    public function share(Request $request): array
    {
        $user = Auth::guard('customer')->user();
        if ($user) { $user->load('profile'); }

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
        ]);
    }

    private function resolveShopContext()
    {
        $contextService = app(\App\Services\ShopContextService::class);
        $activeBranchId = $contextService->getActiveBranchId(); // Ya es String UUID

        $activeBranchName = 'Tienda';
        if ($activeBranchId) {
            $branch = \App\Models\Branch::find($activeBranchId); 
            if ($branch) $activeBranchName = $branch->name;
        }

        return [
            'branch_id'   => $activeBranchId, 
            'branch_name' => $activeBranchName,
        ];
    }

    private function toHex(mixed $value): mixed
    {
        if (is_string($value) && strlen($value) === 16 && !ctype_print($value)) {
            return bin2hex($value);
        }
        return $value;
    }
}